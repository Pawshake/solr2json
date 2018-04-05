<?php

namespace Pawshake\Solr2json\Transformer;

class Transformer
{
    public function transform(array $data)
    {
        $geocode = $data['locm_field_host_geocode'];
        $location = explode(',', $geocode[0]);

        $location = [
            'lat' => (float) $location[0],
            'lon' => (float) $location[1],
        ];

        $serviceRates = [];

        if (isset($data['bm_field_host_pet_boarding'], $data['fts_field_host_rate_per_night'])
            && $data['bm_field_host_pet_boarding'][0] === 1) {
            $serviceRates['petBoarding'] = (float) reset($data['fts_field_host_rate_per_night']);
        }
        if (isset($data['bm_field_host_walking'], $data['fts_field_dog_walking_rate'])
            && $data['bm_field_host_walking'][0] === 1) {
            $serviceRates['dogWalking'] = (float) reset($data['fts_field_dog_walking_rate']);
        }

        if (isset($data['bm_field_host_daycare_in_my_home'], $data['fts_field_host_doggy_day_care_ra'])
            && $data['bm_field_host_daycare_in_my_home'][0] === 1) {
            $serviceRates['doggyDayCare'] = (float) reset($data['fts_field_host_doggy_day_care_ra']);
        }
        if (isset($data['bs_home_visits'], $data['fts_field_host_rate_two_home_vis'])) {
            $serviceRates['homeVisits'] = (float) reset($data['fts_field_host_rate_two_home_vis']);
        }
        if (isset($data['bs_home_visits'], $data['fts_field_host_rate_one_home_vis'])) {
            $serviceRates['homeVisits'] = (float) reset($data['fts_field_host_rate_one_home_vis']);
        }

        if (isset($data['bm_field_host_sleepover'], $data['fts_field_host_rate_sleepover'])
            && $data['bm_field_host_sleepover'][0] === 1) {
            $serviceRates['sleepover'] = (float) reset($data['fts_field_host_rate_sleepover']);
        }

        return [
            'userId' => (string) $data['is_uid'],
            'userName' => str_replace(["\n", "\r", chr(10), chr(13)], '', (string) $data['ss_initials']),
            'sitterId' => (string) $data['entity_id'],
            'profileImageUrl' => (string) $data['ss_ms_user_image_url'],
            'sitterName' => str_replace(["\n", "\r", chr(10), chr(13)], '', (string) $data['label']),
            'latitude' => $location['lat'],
            'longitude' => $location['lon'],
            'reviewCount' => (int) (isset($data['is_reviews']) ? $data['is_reviews'] : 0),
            'rating' => (int) $data['fs_rating'],
            'sellerScore' => (int) (isset($data['is_seller_score']) ? $data['is_seller_score'] : 0),
            'currency' => (string) $data['ss_currency_raw'],
            'rates' => $serviceRates,
            'unavailable' => [
                'general' => isset($data['dm_inavailable']) ? $data['dm_inavailable'] : [],
                'petBoarding' => isset($data['dm_inavailable_1']) ? $data['dm_inavailable_1'] : [],
                'doggyDayCare' => isset($data['dm_inavailable_7']) ? $data['dm_inavailable_7'] : [],
                'dogWalking' => isset($data['dm_inavailable_6']) ? $data['dm_inavailable_6'] : [],
                'homeVisits' => isset($data['dm_inavailable_100']) ? $data['dm_inavailable_100'] : [],
                'sleepover' => isset($data['dm_inavailable_4']) ? $data['dm_inavailable_4'] : [],
            ]

        ];
    }
}
