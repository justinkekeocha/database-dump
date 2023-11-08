<?php

//https://chat.openai.com/c/f91d74db-aaa9-4988-96bf-54340187787a

it('checks if this package is installed', function () {
    // You can directly check for the existence of a class provided by the package.
    // Replace 'Some\Package\ClassName' with a class name from the package you want to check.
    $this->assertTrue(class_exists('Justinkekeocha\DatabaseDump\DatabaseDumpServiceProvider'));
});

// Or check if the package is present in the composer.lock file
it('ensures the package is listed in composer.lock', function () {

    $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
    $packages = array_merge($composerLock['packages'], $composerLock['packages-dev']);

    // Replace 'vendor/package' with the vendor and package name you are checking for.
    $packageInstalled = false;
    foreach ($packages as $package) {
        if ($package['name'] == 'justinkekeocha/database-dump') {
            $packageInstalled = true;
            break;
        }
    }

    $this->assertTrue($packageInstalled);
});

it('checks if package is enabled', function () {
    $this->assertTrue(config('database-dump.enable'));
});
