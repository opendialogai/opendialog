<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LocalPackages extends Command
{
    protected $signature = 'packages:install';

    protected $description = 'Installs the OD core and webchat packages locally for development purposes';

    public function handle()
    {
        $core = "INSTALL_CORE=false";
        $webchat = "INSTALL_WEBCHAT=false";
        $installScript = base_path('scripts/install-local-repos.sh');
        $composerUpdateScript = base_path('scripts/composer-update.sh');

        $composer = $this->createComposerCopy();

        if ($this->confirm('Install OD core locally?')) {
            $core = "INSTALL_CORE=true";

            $composer = $this->setRequirementVersion($composer, 'core');
            $composer = $this->addLocalRepository($composer, 'core');
        }

        if ($this->confirm('Install OD webchat locally?')) {
            $webchat = "INSTALL_WEBCHAT=true";

            $composer = $this->setRequirementVersion($composer, 'webchat');
            $composer = $this->addLocalRepository($composer, 'webchat');
        }

        $this->saveComposerDev($composer);

        $this->info('Installing local dependencies');
        passthru(sprintf("%s %s bash %s", $core, $webchat, $installScript));

        $this->info('Updating composer');
        passthru(sprintf("bash %s", $composerUpdateScript));


        $this->info('Deleting the temp composer file');
        unlink(base_path('composer-dev.json'));

        $this->info('OpenDialog package dependencies all set up');
    }

    public function createComposerCopy()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        if (!isset($composer['repositories'])) {
            $composer['repositories'] = [];
        }
        return $composer;
    }

    public function setRequirementVersion($composer, $package)
    {
        if (isset($composer['require']["opendialogai/$package"])) {
            $composer['require']["opendialogai/$package"] = "*";
        }
        return $composer;
    }

    public function addLocalRepository($composer, $package)
    {
        $composer['repositories'][] = [
            "type" => "path",
            "url" => "./vendor-local/opendialog-$package",
            "options" => [
                "symlink" => true
            ]
        ];
        return $composer;
    }

    public function saveComposerDev($composer): void
    {
        file_put_contents(base_path('composer-dev.json'), json_encode($composer, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
    }
}
