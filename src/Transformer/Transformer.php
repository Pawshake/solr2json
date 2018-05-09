<?php

namespace Pawshake\Solr2json\Transformer;

class Transformer
{
    public function transform(array $data)
    {
        $geocode = $data['locs_field_host_geocode'];
        $location = explode(',', $geocode[0]);

        $location = [
            'lat' => (float)$location[0],
            'lon' => (float)$location[1],
        ];

        $serviceRates = [];

        if (isset($data['bm_field_host_pet_boarding'], $data['fts_field_host_rate_per_night'])
            && $data['bm_field_host_pet_boarding'][0] === 1) {
            $serviceRates['petBoarding'] = (float)reset($data['fts_field_host_rate_per_night']);
        }
        if (isset($data['bm_field_host_walking'], $data['fts_field_dog_walking_rate'])
            && $data['bm_field_host_walking'][0] === 1) {
            $serviceRates['dogWalking'] = (float)reset($data['fts_field_dog_walking_rate']);
        }

        if (isset($data['bm_field_host_daycare_in_my_home'], $data['fts_field_host_doggy_day_care_ra'])
            && $data['bm_field_host_daycare_in_my_home'][0] === 1) {
            $serviceRates['doggyDayCare'] = (float)reset($data['fts_field_host_doggy_day_care_ra']);
        }
        if (isset($data['bs_home_visits'], $data['fts_field_host_rate_two_home_vis'])) {
            $serviceRates['homeVisits'] = (float)reset($data['fts_field_host_rate_two_home_vis']);
        }
        if (isset($data['bs_home_visits'], $data['fts_field_host_rate_one_home_vis'])) {
            $serviceRates['homeVisits'] = (float)reset($data['fts_field_host_rate_one_home_vis']);
        }

        if (isset($data['bm_field_host_sleepover'], $data['fts_field_host_rate_sleepover'])
            && $data['bm_field_host_sleepover'][0] === 1) {
            $serviceRates['sleepover'] = (float)reset($data['fts_field_host_rate_sleepover']);
        }

        $lastBookedTimestamp = (int)$data['is_lb'];
        $lastContactedTimestamp = (int)$data['is_lc'];
        $averageResponseTimeInSeconds = isset($data['avg_response_time_int_all']) ? $data['avg_response_time_int_all'] : 0;

        $pets = [];
        if ($data['is_nr_of_dogs']) {
            $pets['dogs'] = $data['is_nr_of_dogs'];
        }

        return [
            'userId' => (string)$data['is_uid'],
            'userName' => $this->cleanUpDrupalString($data['ss_initials']),
            'sitterId' => (string)$data['entity_id'],
            'profileImageUrl' => (string)$data['ss_ms_user_image_url'],
            'backdropImageUrl' => isset($data['ss_ms_user_image_square_url']) ? (string)$data['ss_ms_user_image_square_url'] : null,
            'sitterName' => $this->cleanUpDrupalString($data['label']),
            'shortDescription' => isset($data['ss_short_content']) ? $this->cleanUpDrupalString($data['ss_short_content']) : null,
            'latitude' => $location['lat'],
            'longitude' => $location['lon'],
            'reviewCount' => (int)(isset($data['is_reviews']) ? $data['is_reviews'] : 0),
            'starRating' => (int)round((int)$data['fs_rating'] / 20, 0, PHP_ROUND_HALF_UP),
            'sellerScore' => (int)(isset($data['is_seller_score']) ? $data['is_seller_score'] : 0),
            'currency' => (string)$data['ss_currency_raw'],
            'sitterSince' => (string)$data['ds_created'],
            'rates' => $serviceRates,
            'unavailable' => [
                'general' => isset($data['dm_inavailable']) ? $data['dm_inavailable'] : [],
                'petBoarding' => isset($data['dm_inavailable_1']) ? $data['dm_inavailable_1'] : [],
                'doggyDayCare' => isset($data['dm_inavailable_7']) ? $data['dm_inavailable_7'] : [],
                'dogWalking' => isset($data['dm_inavailable_6']) ? $data['dm_inavailable_6'] : [],
                'homeVisits' => isset($data['dm_inavailable_100']) ? $data['dm_inavailable_100'] : [],
                'sleepover' => isset($data['dm_inavailable_4']) ? $data['dm_inavailable_4'] : [],
            ],
            'lastActiveOn' => null,
            'recurringGuests' => (int)$data['is_rbookings'],
            'lastBookedOn' => $this->convertToDateTimeImmutable($lastBookedTimestamp),
            'lastContactedOn' => $this->convertToDateTimeImmutable($lastContactedTimestamp),
            'pendingBookings' => (int)$data['is_pb'],
            'sitterPets' => $pets,
            'sitterPetBreed' => $data['ss_breed_name'] ?: null,
            'sitterPetName' => $data['ss_dog_name'] ?: null,
            'responseTimeInHours' => (int)round($averageResponseTimeInSeconds / (60 * 60)),
            'capacity' => [
                'smallDog' => $data['bs_small'] ? 10 : 0,
                'mediumDog' => $data['bs_small'] ? 10 : 0,
                'largeDog' => $data['bs_large'] ? 10 : 0,
                'giantDog' => $data['bs_large'] ? 10 : 0,
                'cat' => $data['bs_small'] ? 10 : 0,
                'smallAnimal' => $data['bs_small'] ? 10 : 0,
            ],
            'sitterHasFullyFencedBackyard' => isset($data['bm_field_host_fenced_backyard']) ? $data['bm_field_host_fenced_backyard'][0] : null,
            'sitterHasNoDog' => isset($data['bs_has_no_dogs']) ? $data['bs_has_no_dogs'] : null,
            'sitterHasNoChildren' => isset($data['bs_has_no_children']) ? $data['bs_has_no_children'] : null
        ];
    }

    private function cleanUpDrupalString(string $string)
    {
        $htmlEntityDecoded = html_entity_decode(
            str_replace(["\n", "\r"], '', $string),
            ENT_QUOTES
        );

        $withoutNonBreakingWhiteSpaces = str_replace("\xc2\xa0", ' ', $htmlEntityDecoded);

        return trim($withoutNonBreakingWhiteSpaces);
    }

    private function convertToDateTimeImmutable($timestamp)
    {
        return $timestamp ? (new \DateTimeImmutable())->setTimestamp($timestamp)->format('c') : null;
    }
}
