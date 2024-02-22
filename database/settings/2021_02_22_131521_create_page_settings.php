<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePageSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('page-settings.privacy', null);
        $this->migrator->add('page-settings.terms_of_service', null);
    }
}
