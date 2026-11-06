<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('company.name', ['ar' => 'هديب للحديد والمعدات', 'en' => 'HDAIB Engineering Co. — HEC']);
        $this->migrator->add('company.legal_name', ['ar' => 'هديب للحديد والمعدات', 'en' => 'HDAIB Engineering Co.']);
        $this->migrator->add('company.legal_name_confirmed', false);
        $this->migrator->add('company.tagline', ['ar' => 'حديد ومعدات لاحتياجات الورش والمشاريع', 'en' => 'Steel and equipment for workshops and projects']);
        $this->migrator->add('company.tagline_approved', false);
        $this->migrator->add('company.phone', '0788878327');
        $this->migrator->add('company.phone_confirmed', false);
        $this->migrator->add('company.whatsapp', '0781221617');
        $this->migrator->add('company.whatsapp_confirmed', false);
        $this->migrator->add('company.public_email', 'hdaibahmad8@gmail.com');
        $this->migrator->add('company.public_email_confirmed', false);
        $this->migrator->add('company.quote_notification_email', 'hdaibahmad8@gmail.com');
        $this->migrator->add('company.address', ['ar' => 'عمّان — الرجيب', 'en' => 'Amman — Al-Rujeib']);
        $this->migrator->add('company.maps_url', null);
        $this->migrator->add('company.latitude', null);
        $this->migrator->add('company.longitude', null);
        $this->migrator->add('company.location_confirmed', false);
        $this->migrator->add('company.working_hours', []);
        $this->migrator->add('company.working_hours_note', ['ar' => '', 'en' => '']);
        $this->migrator->add('company.working_hours_confirmed', false);
        $this->migrator->add('company.service_areas', ['ar' => '', 'en' => '']);
        $this->migrator->add('company.social', ['facebook' => 'https://web.facebook.com/HECjordan']);
    }
};
