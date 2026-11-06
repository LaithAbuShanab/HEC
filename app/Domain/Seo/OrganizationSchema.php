<?php

namespace App\Domain\Seo;

use App\Domain\Company\CompanyProfile;

final class OrganizationSchema
{
    public static function build(string $locale): array
    {
        $profile = app(CompanyProfile::class)->toArray($locale);
        $sameAs = array_values($profile['social']);

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => $profile['legalName'] ?? $profile['name'],
            'alternateName' => 'HEC',
            'url' => url($locale),
            'logo' => url('/images/brand/mark-color.png'),
            'email' => $profile['email'],
            'telephone' => $profile['phone']['display'] ?? null,
            'sameAs' => $sameAs === [] ? null : $sameAs,
        ]);

        $schemas = [$organization];

        if ($profile['address'] !== null) {
            $schemas[] = array_filter([
                '@type' => 'LocalBusiness',
                '@id' => url('/#business'),
                'name' => $profile['name'],
                'url' => url($locale),
                'image' => url('/images/brand/og-default.jpg'),
                'telephone' => $profile['phone']['display'] ?? null,
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => $profile['address'], 'addressLocality' => 'Amman', 'addressCountry' => 'JO'],
                'geo' => $profile['geo'] === null ? null : ['@type' => 'GeoCoordinates', 'latitude' => $profile['geo']['lat'], 'longitude' => $profile['geo']['lng']],
                'hasMap' => $profile['mapsUrl'],
                'openingHoursSpecification' => $profile['hours'] === null ? null : array_map(fn (array $slot) => [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => array_map(fn (string $day) => ucfirst($day), $slot['days']),
                    'opens' => $slot['opens'],
                    'closes' => $slot['closes'],
                ], $profile['hours']),
                'parentOrganization' => ['@id' => url('/#organization')],
            ]);
        }

        return $schemas;
    }
}
