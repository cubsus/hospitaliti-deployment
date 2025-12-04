{{--
    Laravel Envoy Deployment Script
    This script handles zero-downtime deployments to production servers

    Usage: php vendor/bin/envoy run deploy

    (If a rollback is needed, push a correcting commit to GitHub and deploy again)
--}}

@include('./vendor/autoload.php');

@setup
    // Get current working directory
    $wd = dirname( __FILE__ );

    // Initialize dotenv to load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

    // Load and validate required environment variables from .env file
    try {
        $dotenv->load();
        $dotenv->required(['DEPLOY_USER'])->notEmpty();       // SSH user for deployment
        $dotenv->required(['DEPLOY_HOST'])->notEmpty();       // Server hostname or IP
        $dotenv->required(['DEPLOY_REPOSITORY'])->notEmpty(); // Git repository URL
        $dotenv->required(['DEPLOY_RELEASE_DIR'])->notEmpty(); // Directory where releases are stored
        $dotenv->required(['DEPLOY_APP_DIR'])->notEmpty();    // Main application directory
    } catch ( Exception $e )  {
        echo $e->getMessage(); exit;
    }

    // Define constants for SSH connection
    define('DEPLOY_USER', $_ENV['DEPLOY_USER']);
    define('DEPLOY_HOST', $_ENV['DEPLOY_HOST']);

    // Setup deployment paths and variables
    $repository = $_ENV['DEPLOY_REPOSITORY'];           // Git repository URL
    $releases_dir = $_ENV['DEPLOY_RELEASE_DIR'];       // Directory containing all releases
    $app_dir = $_ENV['DEPLOY_APP_DIR'];                // Main application directory
    $release = date('YmdHis');                          // Unique release identifier (timestamp)
    $new_release_dir = $releases_dir .'/'. $release;   // Full path to new release
    $branch = $_ENV['DEPLOY_BRANCH'] ?? 'main';      // Git branch to deploy (defaults to 'main')
    $month = date('Ym', strtotime('-1 month'));        // Used for cleanup of old releases

    // ============================================================================
    // CI/CD Status Check - Prevents deployment if tests are failing
    // ============================================================================
    // Extract repository owner/name from the Git URL (e.g., git@github.com:owner/repo.git -> owner/repo)
    $repoPath = preg_replace('/^.*[:\\/]([^\\/]+\\/[^\\/]+?)(?:\\.git)?$/', '$1', $repository);

    // Get the latest CI run from GitHub Actions for the main branch
    $ciRun = trim(shell_exec('gh run list --repo ' . escapeshellarg($repoPath) . ' --branch main --limit 1 --json conclusion,headSha,displayTitle'));

    // Check if we got any CI runs
    if (empty($ciRun)) {
        echo "No CI runs found for main branch.\n";
        exit;
    }

    // Parse the JSON response from GitHub CLI
    $ciData = json_decode($ciRun, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($ciData)) {
        echo "Failed to parse CI data.\n";
        exit;
    }

    // Extract the latest run information
    $latestRun = $ciData[0];
    $ciStatus = $latestRun['conclusion'] ?? 'unknown';

    // Only allow deployment if CI status is 'success'
    if ($ciStatus !== 'success') {
        echo "Last commit did not succeed in tests.\n";
        echo "Commit: " . ($latestRun['displayTitle'] ?? 'unknown') . "\n";
        echo "CI Status: " . $ciStatus . "\n";
        exit;
    }

    // Store the commit hash from the successful CI run for deployment
    $commit = $latestRun['headSha'];

    // ============================================================================
    // Helper Functions
    // ============================================================================

    /**
     * Output a success message in green color
     */
    function logMessage($message) {
        return "echo '\033[32m" .$message. "\033[0m';\n";
    }
@endsetup

{{--
    Server Configuration
    Defines the remote server connection using SSH
--}}
@servers(['live' => DEPLOY_USER .'@'. DEPLOY_HOST])

{{--
    Deployment Story
    Orchestrates the complete deployment process in sequence
    - confirm: false means no manual confirmation required before running
--}}
@story('deploy', ['on' => 'live', 'confirm' => false])
    clone-repository
    update-symlinks
    run-composer
    run-node
    switch-release
    clean-old-releases
@endstory

{{--
    Task: Clone Repository
    Clones the git repository to a new timestamped release directory
--}}
@task('clone-repository', ['on' => 'live'])
    {{ logMessage("Cloning repository") }}
    [ -d {{ $app_dir }} ] || mkdir {{ $app_dir }}
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone {{ $repository }} --branch={{ $branch }} --depth 1 -q {{ $new_release_dir }}
    cd {{ $new_release_dir }}
    git reset --hard {{ $commit }}
@endtask

{{--
    Task: Update Symlinks
    Creates symbolic links for shared resources (storage, .env)
    The release is built in isolation before being switched
--}}
@task('update-symlinks', ['on' => 'live'])
    {{ logMessage("Linking storage directory") }}
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage

    {{ logMessage("Linking .env file") }}
    ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env
@endtask

{{--
    Task: Run Composer
    Installs PHP dependencies and runs Laravel optimization commands
--}}
@task('run-composer', ['on' => 'live'])
    echo "Starting deployment ({{ $release }})"
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts -q -o
    php artisan migrate --force
    php artisan clear-compiled
    php artisan config:cache
    composer dump-autoload
    php artisan optimize:clear
    php artisan horizon:terminate
@endtask

{{--
    Task: Run Node
    Installs npm dependencies and builds frontend assets
--}}
@task('run-node', ['on' => 'live'])
    cd {{ $new_release_dir }}
    npm install
    export NODE_OPTIONS="--max-old-space-size=6144"
    npm run build
@endtask

{{--
    Task: Switch Release
    Atomically switches the 'current' symlink to the new release
    This enables zero-downtime deployment - the release is fully built before switching
--}}
@task('switch-release', ['on' => 'live'])
    {{ logMessage("Switching to new release") }}
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current

    {{ logMessage("Linking storage to public") }}
    cd {{ $new_release_dir }}
    php artisan storage:link
@endtask

{{--
    Task: Clean Old Releases
    Removes old release directories to save disk space
    Keeps only the 5 most recent releases for easy rollback
--}}
@task('clean-old-releases', ['on' => 'live'])
    {{ logMessage('Delete all but the 5 most recent releases') }}

    ls -dt {{ $releases_dir }}/* | tail -n +6 | xargs -d "\n" rm -rf
@endtask
