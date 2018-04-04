<?php

namespace Pawshake\Solr2json\Transformer;

class Transformer {

    /**
     * @param array $data
     *
     * @return array
     */
    public function transform(array $data) : array {

        $geocode = $data['locm_field_host_geocode'];
        $location = explode(',', $geocode[0]);

        $location = [
            'lat' => (float) $location[0],
            'lon' => (float) $location[1],
        ];

        $services = [];
        $ratings = [];

        if (isset($data['bm_field_host_pet_boarding'], $data['fts_field_host_rate_per_night']) && $data['bm_field_host_pet_boarding'][0] === 1) {
            $services[] = Services::PET_BOARDING;
            $result = $data['fts_field_host_rate_per_night'];
            $ratings['pet_boarding'] = (float) reset($result);
        }
        if (isset($data['bm_field_host_walking'], $data['fts_field_dog_walking_rate']) && $data['bm_field_host_walking'][0] === 1) {
            $services[] = Services::DOG_WALKING;
            $result = $data['fts_field_dog_walking_rate'];
            $ratings['dog_walking'] = (float) reset($result);
        }

        if (isset($data['bm_field_host_daycare_in_my_home'], $data['fts_field_host_doggy_day_care_ra']) && $data['bm_field_host_daycare_in_my_home'][0] === 1) {
            $services[] = Services::DOGGY_DAY_CARE;
            $result = $data['fts_field_host_doggy_day_care_ra'];
            $ratings['doggy_day_care'] = (float) reset($result);
        }
        if (isset($data['bs_home_visits'], $data['bs_home_visits'])) {
            $ratingFound = false;
            if (isset($data['fts_field_host_rate_one_home_vis'])) {
                $one_visit_result = $data['fts_field_host_rate_one_home_vis'];
                $ratings['home_visits']['one_visit'] = (float) reset($one_visit_result);
                $ratingFound = true;
            }

            if (isset($data['fts_field_host_rate_two_home_vis'])) {
                $two_visit_result = $data['fts_field_host_rate_two_home_vis'];
                $ratings['home_visits']['two_visit'] = (float) reset($two_visit_result);
                $ratingFound = true;
            }

            if ($ratingFound) {
                $services[] = Services::HOME_VISITS;
            }
        }
        if (isset($data['bm_field_host_sleepover'], $data['fts_field_host_rate_sleepover']) && $data['bm_field_host_sleepover'][0] === 1) {
            $services[] = Services::SLEEPOVER;
            $result = $data['fts_field_host_rate_sleepover'];
            $ratings['sleepover'] = (float) reset($result);
        }

        $global_ratings = [
            'petboarding' => $data['bs_petboarding'] ?? false,
            'petboarding_rate' => (float) ($data['fs_petboarding_rate'] ?? 0),
            'petsitting' => $data['bs_petsitting'] ?? false,
            'petsitting_rate_minimum' => (float) ($data['fs_petsitting_rate_minimum'] ?? 0),
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
            'id' => (int) $data['entity_id'],
            'profile_image' => $data['ss_ms_user_image_url'],
            'name' => $data['ss_initials'],
            'uid' => (int) $data['is_uid'],
            'title' => $data['label'],
            'address' => $data['ss_locality'],
            'location' => $location,
            'reviews' => (int) ($data['is_reviews'] ?? 0),
            'has_video' => $data['bs_has_video'],
            'rating' => $data['fs_rating'],
            'services' => $services,
            'rates' => [
                'global' => $global_ratings,
                'services' => $ratings,
            ],
            'seller_score' => (int) ($data['is_seller_score'] ?? 0),
            'seller_score_v2' => (int) ($data['is_seller_score_v2'] ?? 0),
            'currency' => [
                'currency' => $data['ss_currency_raw'],
                'symbol' => $data['ss_currency'],
            ],
            'created' => $data['ds_created'],
            'short_content' => $data['ss_short_content'],
            'language' => $data['ss_language'],
            'pending_bookings' => (int) $data['is_pb'],
            'recurring_bookings' => (int) $data['is_rbookings'],
            'inquiry_info' => $inquiry_info,
            'pm_info' => $pm_info,
            'last_booked' => (int) $data['is_lb'],
            'last_contacted' => (int) $data['is_lc'],
            'availability' => $availability,
            'unavailable' => [
                'general' => $data['dm_inavailable'] ?? [],
                Services::PET_BOARDING => $data['dm_inavailable_1'] ?? [],
                Services::DOGGY_DAY_CARE => $data['dm_inavailable_7'] ?? [],
                Services::DOG_WALKING => $data['dm_inavailable_6'] ?? [],
                Services::HOME_VISITS => $data['dm_inavailable_100'] ?? [],
                Services::SLEEPOVER => $data['dm_inavailable_4'] ?? [],
            ],
        ];
    }
}
