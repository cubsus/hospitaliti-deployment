<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('getModels')) {
    function getModels()
    {
        $path = app_path().'/Models';
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' || $result === '..') {
                continue;
            }
            $filename = $path.'\\'.$result;
            if (is_dir($filename)) {
                $out = array_merge($out, getModels());
            } else {
                $out[] = substr($filename, 0, -4);
            }
        }

        return $out;
    }
}

if (! function_exists('authUserIsSuperAdmin')) {
    function authUserIsSuperAdmin()
    {
        /** @var \App\Models\User */
        $authenticatedUser = Auth::user();

        if ($authenticatedUser != null) {
            if ($authenticatedUser->hasRole('Super Admin')) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}

if (! function_exists('authUserIsDeveloper')) {
    function authUserIsDeveloper()
    {
        /** @var \App\Models\User */
        $authenticatedUser = Auth::user();

        if ($authenticatedUser != null) {
            if ($authenticatedUser->hasRole('Developer')) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}
