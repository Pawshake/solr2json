<?php

namespace Pawshake\Solr2json\Transformer;

class Transformer
{
    const PET_BOARDING = 'pet_boarding';
    const DOGGY_DAY_CARE = 'doggy_day_care';
    const DOG_WALKING = 'dog_walking';
    const HOME_VISITS = 'home_visits';
    const SLEEPOVER = 'sleepover';

    public function transform(array $data)
    {

        $geocode = $data['locm_field_host_geocode'];
        $location = explode(',', $geocode[0]);

        $location = [
            'lat' => (float) $location[0],
            'lon' => (float) $location[1],
        ];

        $services = [];
        $serviceRates = [];

        if (isset($data['bm_field_host_pet_boarding'], $data['fts_field_host_rate_per_night']) && $data['bm_field_host_pet_boarding'][0] === 1) {
            $services[] = self::PET_BOARDING;
            $result = $data['fts_field_host_rate_per_night'];
            $serviceRates['pet_boarding'] = (float) reset($result);
        }
        if (isset($data['bm_field_host_walking'], $data['fts_field_dog_walking_rate']) && $data['bm_field_host_walking'][0] === 1) {
            $services[] = self::DOG_WALKING;
            $result = $data['fts_field_dog_walking_rate'];
            $serviceRates['dog_walking'] = (float) reset($result);
        }

        if (isset($data['bm_field_host_daycare_in_my_home'], $data['fts_field_host_doggy_day_care_ra']) && $data['bm_field_host_daycare_in_my_home'][0] === 1) {
            $services[] = self::DOGGY_DAY_CARE;
            $result = $data['fts_field_host_doggy_day_care_ra'];
            $serviceRates['doggy_day_care'] = (float) reset($result);
        }
        if (isset($data['bs_home_visits'], $data['bs_home_visits'])) {
            if (isset($data['fts_field_host_rate_one_home_vis'])) {
                $one_visit_result = $data['fts_field_host_rate_one_home_vis'];
                $serviceRates['home_visits'] = (float) ($one_visit_result);
                $services[] = self::HOME_VISITS;
            }

        }
        if (isset($data['bm_field_host_sleepover'], $data['fts_field_host_rate_sleepover']) && $data['bm_field_host_sleepover'][0] === 1) {
            $services[] = self::SLEEPOVER;
            $result = $data['fts_field_host_rate_sleepover'];
            $serviceRates['sleepover'] = (float) reset($result);
        }

        $global_ratings = [
            'petboarding' => isset($data['bs_petboarding']) ? $data['bs_petboarding'] : false,
            'petboarding_rate' => (float) (isset($data['fs_petboarding_rate']) ? $data['fs_petboarding_rate'] : 0),
            'petsitting' => isset($data['bs_petsitting']) ? $data['bs_petsitting'] : false,
            'petsitting_rate_minimum' => (float) (isset($data['fs_petsitting_rate_minimum']) ? $data['fs_petsitting_rate_minimum'] : 0),
        ];

        $inquiry_info = unserialize($data['zs_inquiry_info'], ['allowed_classes' => true]);
        $integers = [
            'unique_users_inquiry',
            'unique_users_pre_approved',
            'unique_users_pre_approved_and_made_a_booking',
            'unique_users_declined',
            'unique_users_direct_booking',
            'unique_users_booking_all',
            'unique_users_pre_approved_90d',
            'unique_users_pre_approved_and_made_a_booking_90d',
            'total_inquiries',
            'total_inquiries_preapproved',
            'total_inquiries_declined',
            'total_inquiries_booking',
            'unique_users_that_made_a_booking',
        ];
        foreach ($integers as $integer) {
            if (isset($inquiry_info[$integer])) {
                $inquiry_info[$integer] = (int) $inquiry_info[$integer];
            }
        }
        $pm_info = unserialize($data['zs_pm_info'], ['allowed_classes' => true]);
        $integers = [
            'threads_all',
            'messages_all',
            'answered_all',
            'threads_all_rate',
            'answered_all_rate',
            'threads_all_rate_45_days',
            'answered_all_rate_45_days',
            'threads_all_rate_7_days',
            'answered_all_rate_7_days',
            'answered_all_rate_7_days',
            'response_rate_7_days',
            'unread',
            'unanswered_all',
            'avg_response_time_int_all',
            'unanswered_45',
            'avg_response_time_int_45',
            'unanswered_14',
            'avg_response_time_int_14',
            'unanswered_7',
            'response_rate_7_days',
            'avg_response_time_int_7',
            'unique_users_life_time',
            'unique_users_90_days',
        ];
        foreach ($integers as $integer) {
            if (isset($pm_info[$integer])) {
                $pm_info[$integer] = (int) $pm_info[$integer];
            }
        }
        $floats = [
            'response_rate',
            'response_rate_45_days',
        ];
        foreach ($floats as $float) {
            if (isset($pm_info[$float])) {
                $pm_info[$float] = (float) $pm_info[$float];
            }
        }

        $availability = unserialize($data['zs_availability'], ['allowed_classes' => true]);

        return [
            'userId' => (string) $data['is_uid'],
            'userName' => (string) $data['ss_initials'],
            'sitterId' => (string) $data['entity_id'],
            'profileImageUrl' => (string) $data['ss_ms_user_image_url'],
            'sitterName' => (string) $data['label'],
            'latitude' => $location['lat'],
            'longitude' => $location['lon'],
            'reviewCount' => (int) (isset($data['is_reviews']) ? $data['is_reviews'] : 0),
            'rating' => (int) $data['fs_rating'],
            'sellerScore' => (int) (isset($data['is_seller_score']) ? $data['is_seller_score'] : 0),
            'globalRate' => (int) (isset($data['rates']['global']) ? $data['rates']['global'] : 0), // nullable ?
            'description' => (string) $data['ss_short_content'],
            'language' => (string) $data['ss_language'],
            'sitterSince' => $data['ds_created'],
            'currency' => (string) $data['ss_currency_raw'],
            'rates' => $serviceRates,
            'unavailable' => [
                'general' => $data['dm_inavailable'] ?? [],
                self::PET_BOARDING => $data['dm_inavailable_1'] ?? [],
                self::DOGGY_DAY_CARE => $data['dm_inavailable_7'] ?? [],
                self::DOG_WALKING => $data['dm_inavailable_6'] ?? [],
                self::HOME_VISITS => $data['dm_inavailable_100'] ?? [],
                self::SLEEPOVER => $data['dm_inavailable_4'] ?? [],
            ]
            /*
            'address' => $data['ss_locality'],
            'has_video' => $data['bs_has_video'],
            'services' => $services,
            'seller_score_v2' => (int) ($data['is_seller_score_v2'] ?? 0),
            'currency' => [
                'currency' => $data['ss_currency_raw'],
                'symbol' => $data['ss_currency'],
            ],
            'pending_bookings' => (int) $data['is_pb'],
            'recurring_bookings' => (int) $data['is_rbookings'],
            'inquiry_info' => $inquiry_info,
            'pm_info' => $pm_info,
            'last_booked' => (int) $data['is_lb'],
            'last_contacted' => (int) $data['is_lc'],
            */
        ];
    }
}
