<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddCustomModelToAiSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ai.custom_model', null);
    }
}
