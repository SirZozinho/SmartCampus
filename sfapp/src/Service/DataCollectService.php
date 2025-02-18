<?php

namespace App\Service;

use App\Repository\RoomRepository;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

class DataCollectService
{
    /**
     * @var HttpClientInterface $httpClient Interface to communicate with API
     * @author Louis PAQUEREAU
     */
    private HttpClientInterface $httpClient;

    /**
     * @var CacheInterface Cache interface to store received and sorted data
     * @author Louis PAQUEREAU
     */
    private CacheInterface $cache;


    /**
     * @param HttpClientInterface $httpClient
     * @param CacheInterface $cache
     * @author Louis PAQUEREAU
     */
    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }


    /**
     * @brief Get raw data in function of a date interval from API
     * @param DateTime $from the start date
     * @param DateTime $to the end date
     * @param RoomRepository $roomRepository
     * @return array return an array from json decoding
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @author Louis PAQUEREAU
     */
    public function getCapturesBetweenDatesFromAPI(DateTime $from, DateTime $to, RoomRepository $roomRepository): array {
        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/interval'; //Defines the correct url to search in the correct way
        $rooms = $roomRepository->findAll(); //Defines all registered rooms
        $allData = []; //Defines the returned list with all Json data converted to an array

        /* The DBList
        * Permits to know in which database we need to search to go
        * to search data for the correct room.
        *
        * Upgrade ideas:
        * -> Integrate database name into the entity.
        * The problem is that the user needs to know where the data is stored
        *
        * -> Upgrade the API to get one database with one table for all captures
        */
        $dbList = [
            "D205" => "sae34bdk1eq1",
            "D206" => "sae34bdk1eq2",
            "D207" => "sae34bdk1eq3",
            "D204" => "sae34bdk2eq1",
            "D203" => "sae34bdk2eq2",
            "D303" => "sae34bdk2eq3",
            "D304" => "sae34bdl1eq1",
            "C101" => "sae34bdl1eq2",
            "D109" => "sae34bdl1eq3",
            "Secrétariat" => "sae34bdl2eq1",
            "D001" => "sae34bdl2eq2",
            "D002" => "sae34bdl2eq3",
            "D004" => "sae34bdm1eq1",
            "C004" => "sae34bdm1eq2",
            "C007" => "sae34bdm1eq3",
        ];

        /*
         * Data fetching
         *
         * We need to travel each database to get every data for a specific room.
         * We call API in each database to get every value in known databases
         *
         * Upgrade ideas:
         * -> Upgrade the API to get one database with one table for all captures
         *
         */
        foreach ($rooms as $room) {
            $roomName = $room->getName(); //Defines the name of actual rooms
            $dbName = $dbList[$roomName] ?? null; //Defines the target database for looped room


            /* The API call
            *
            * The objective is to do an HTTP request to get our data
            *
            * In that request we have:
            *  - Header (invisible and encrypted if URL is HTTPS)
            *  - Query (We can see it on the adress bar on the web browser)
            *
            * The response contains :
            * - Header
            * - Content (We need that especially)
            */
            try {
                $response = $this->httpClient->request('GET',$url, [
                    'headers' =>[
                        'username' => 'l2eq3', //Defines the username to connect to the database
                        'dbname' => $dbName, //Defines the target database for the target room
                        'userpass' => 'jobjih-Nizwe9-xusvyv', //Defines the password to connect to the database
                    ],
                    'query' => [
                        'date1' => $from->format('Y-m-d'), //Defines the start date interval
                        'date2' => $to->format('Y-m-d'), //Defines the end date interval
                        'page' => 1 //Defines the page. default = 1
                    ],
                ]);

                //Check if response is OK or throw an exception if it's not the case
                if ($response->getStatusCode() !== Response::HTTP_OK) {
                    throw new Exception('Erreur lors de la récupération des données : ' . $response->getStatusCode());
                } else {
                    $data = json_decode($response->getContent(), true);
                    $allData = array_merge($allData, $data); //Merge this into the returned list
                }

            } catch (Exception $e) {
                error_log("Erreur dans DataCollectService".$e->getMessage());
                continue;
            }

        }

        return $allData;
    }


    /**
     * @brief Format raw data received from API for data interval request
     * @param DateTime $startDate The start date
     * @param DateTime $endDate The end date
     * @param RoomRepository $repository The list of functionnal rooms
     * @return array sorted array for statistics measures
     * @throws InvalidArgumentException
     * @auhtor Enzo BIGUET, Louis PAQUEREAU
     */
    public function getCapturesBetweenDates(DateTime $startDate, DateTime $endDate, RoomRepository $repository): array {

        //Generates cache key with start date, end date, and service type to get a unique key
        $cacheKey = sprintf('room_data_%s_%s_%s', $startDate->format('Ymd'), $endDate->format('Ymd'), 'captures_between_dates');

        /* This part chose of returning data stored from cache or from API
         * It avoids to call API foreach page refreshed
         *
         *If there is data stored into the cache associated to that key, values from cache are returned
         *If there is data stored into cache associated to that key, fresh values from API are returned (executes function)
         */
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($startDate, $endDate, $repository) {
            $item->expiresAfter(600); //Defines the expiration of that generated cache

            $result = ["rooms" => [],]; //Defines the final result
            $uniqueDays = []; //Defines unique days
            $aggregatedData = []; //Data aggregated per day/room before calculating averages
            $rawData = $this->getCapturesBetweenDatesFromAPI($startDate, $endDate, $repository); //Defines the raw values fetched from API

            /* Data sorting
             *
             * Sort each entry to return an exploitable list to do statistics
             *
             */
            foreach ($rawData as $entry) {
                //Ignore values of datatype pressure
                if ($entry['nom'] === 'pres') {
                    continue;
                }

                //Transform string date into DateTime
                $date = new DateTime($entry['dateCapture'], null);

                //Ignoring data that is out of date interval
                if ($date < $startDate || $date > $endDate) {
                    continue;
                }

                $day = $date->format('Y-m-d'); //Extract the date from DateTime
                $room = $entry['localisation']; //Defines the targeted room of the entry
                $dataType = $entry['nom']; //Defines the target data type of the entry
                $value = is_numeric($entry['valeur']) ? (float)$entry['valeur'] : $entry['valeur']; //Defines the actual value of the entry converted to a numeric value

                //Add each unique days
                if (!in_array($day, $uniqueDays, true)) {
                    $uniqueDays[] = $day;
                }

                //Initiate the list that contains data for a day for a room
                $aggregatedData[$room][$day] = $aggregatedData[$room][$day] ?? [
                    "temp" => [],
                    "hum" => [],
                    "co2" => [],
                ];

                //Add values in function of its type
                if ($dataType === 'temp') {
                    $aggregatedData[$room][$day]["temp"][] = $value;
                } elseif ($dataType === 'hum') {
                    $aggregatedData[$room][$day]["hum"][] = $value;
                } elseif ($dataType === 'co2') {
                    $aggregatedData[$room][$day]["co2"][] = $value;
                }
            }

            /*
             * Calculate the average of temp / humidity / co2 for a day for each room
             */
            foreach ($aggregatedData as $room => $days) {
                //Creates the structure of the list
                $result['rooms'][$room] = $result['rooms'][$room] ?? [
                    "temp" => [],
                    "hum" => [],
                    "co2" => [],
                ];

                //Looping every day in the room
                foreach ($days as $day => $dataTypes) {
                    //Looping each datatype
                    foreach ($dataTypes as $dataType => $values) {
                        //Checks if values is empty
                        if (!empty($values)) {
                            $average = array_sum($values) / count($values); //Sum all values
                            $result['rooms'][$room][$dataType][] = $average; //Store in the list for target room for target data type
                        } else {
                            $result['rooms'][$room][$dataType][] = null; //set null if empty
                        }
                    }
                }
            }

            //Fill the $result of days with all unique days
            $result['days'] = range(0, count($uniqueDays) - 1);

            return $result;
        });
    }

    /**
     * @brief Format raw data received from API for data interval request
     * @param DateTime $startDate The start date
     * @param DateTime $endDate The end date
     * @param RoomRepository $repository The list of functionnal rooms
     * @auhtor Louis PAQUEREAU, Clément MUZELIER--ARTHUS
     * @return array sorted array for all measures
     */
    public function getAllCapturesBetweenDates(DateTime $startDate, DateTime $endDate, RoomRepository $repository, String $roomName,string $dataType): array {

        $cacheKey = sprintf('data_history_%s_%s_%s_%s', $startDate->format('Ymd'), $endDate->format('Ymd'), $roomName, $dataType);

        // Check and retrieve from cache if available
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($startDate, $endDate, $roomName, $repository, $dataType) {
            $item->expiresAfter(600);

            $rawData = $this->getCapturesBetweenDatesFromAPI($startDate, $endDate, $repository);

            // Filtering of data to keep only those corresponding to the given room and type of data
            $captures = [];
            foreach ($rawData as $entry) {
                if (
                    isset($entry['localisation'], $entry['nom'], $entry['valeur'], $entry['dateCapture']) &&
                    $entry['localisation'] === $roomName &&
                    $entry['nom'] === $dataType
                ) {
                    $captures[] = [
                        'datetime' => $entry['dateCapture'],
                        'value' => is_numeric($entry['valeur']) ? (float)$entry['valeur'] : null
                    ];
                }
            }

            // Sort captures in chronological order (ascending dates)
            usort($captures, fn($a, $b) => strtotime($a['datetime']) <=> strtotime($b['datetime']));

            // Transformation into an array containing two lists: ‘dates’ and ‘values’.
            $result = [
                'dates' => [],
                'values' => []
            ];

            foreach ($captures as $capture) {
                $result['dates'][] = $capture['datetime'];
                $result['values'][] = $capture['value'];
            }

            return $result;
        });

    }

    /**
     * @brief gets last raw values from api for a room
     * @param string $systemName the acquisition system that send information
     * @param string $roomName the target room
     * @param string $dataType the type of returned data (Temperature => 'temp', Humidity => 'hum' and co2 => 'co2')
     * @return array the json response of API request as a table
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @author Louis PAQUEREAU
     */
    public function getLastCaptureFromAPI(string $systemName, string $roomName, string $dataType): array {
        //The URL of API to get last capure
        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/last';

        /* The DBList
         * Permits to know in which database we need to search to go
         * to search data for the correct room.
         *
         * Upgrade ideas:
         * -> Integrate database name into the entity.
         * The problem is that the user needs to know where the data is stored
         *
         * -> Upgrade the API to get one database with one table for all captures
         */
        $dbList = [
            "D205" => "sae34bdk1eq1",
            "D206" => "sae34bdk1eq2",
            "D207" => "sae34bdk1eq3",
            "D204" => "sae34bdk2eq1",
            "D203" => "sae34bdk2eq2",
            "D303" => "sae34bdk2eq3",
            "D304" => "sae34bdl1eq1",
            "C101" => "sae34bdl1eq2",
            "D109" => "sae34bdl1eq3",
            "Secrétariat" => "sae34bdl2eq1",
            "D001" => "sae34bdl2eq2",
            "D002" => "sae34bdl2eq3",
            "D004" => "sae34bdm1eq1",
            "C004" => "sae34bdm1eq2",
            "C007" => "sae34bdm1eq3",
        ];


        /* The API call
         *
         * The objective is to do an HTTP request to get our data
         *
         * In that request we have:
         *  - Header (invisible and encrypted if URL is HTTPS)
         *  - Query (We can see it on the adress bar on the web browser)
         *
         * The response contains :
         * - Header
         * - Content (We need that especially)
         */
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'dbname' => $dbList[$roomName], //Defines the target database for the target room
                    'username' => 'l2eq3', //Username to access to the database
                    'userpass' => 'jobjih-Nizwe9-xusvyv', //Password to access to the database
                ],
                'query' => [
                    'nomsa' => $systemName, //The acquisition system name
                    'localisation' => $roomName, //The target room
                    'nom' => $dataType, //The datatype (temperature => 'temp'/ humidity => 'hum'/ co2 => 'co2'
                ],
            ]);

            //Check if response is OK
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception('Erreur lors de la récupération des données : ' . $response->getStatusCode());
            }

            $data = $response->toArray(); //Defines the returned list with all Json data converted to an array


        } catch (Exception $e) {
            //Show error if there is an execption and return an empty list
            error_log('Erreur dans DataCollectService: ' . $e->getMessage());
            return [];
        }

        return $data;
    }


    /**
     * @brief get last data received from API reformated to be read easily
     * @param string $systemName the acquisition system that send information
     * @param string $roomName the target room
     * @return array list that contains temperature, humidity and co2
     * @throws InvalidArgumentException returned if received date from api is not in the correct format
     * @author Louis PAQUEREAU
     */
    public function getLastCaptures(string $systemName, string $roomName): array
    {
        //Generates cache key with start date, end date, and service type to get a unique key
        $cacheKey = sprintf('last_captures_%s_%s', $systemName, $roomName);

        /* This part chose of returning data stored from cache or from API
         * It avoids to call API foreach page refreshed
         *
         *If there is data stored into the cache associated to that key, values from cache are returned
         *If there is data stored into cache associated to that key, fresh values from API are returned (executes function)
         */
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($systemName, $roomName) {
            $item->expiresAfter(600); //Defines the expiration of that generated cache

            /*
             * Fecthing data
             *
             * Upgrade ideas:
             * -> Update the table capture in the API to get temperature, humidity, co2 in the same tuple.
             * That avoids to do 3 request for each room that we need to get his last constants.
             */
            $tempData = $this->getLastCaptureFromAPI($systemName, $roomName, 'temp'); //Defines raw last temperature fetched from API
            $humData = $this->getLastCaptureFromAPI($systemName, $roomName, 'hum');//Defines raw last humidity rate fetched from API
            $co2Data = $this->getLastCaptureFromAPI($systemName, $roomName, 'co2');//Defines raw last co2 amount fetched from API

            /*
             * Checking data received
             *
             * -> Checking if responses from API are null
             * -> Checking if responses from API concerns the target room and not another
             */

            //If data is empty cause of error, returns an error
            if (empty($tempData) || empty($humData) || empty($co2Data)) {
                return ['name' => $roomName, 'error' => 'Données manquantes pour certaines mesures'];
            }

            //Checks if data received from api concerns the target room
            if (array_column($tempData, 'localisation')[0] != $roomName || array_column($humData, 'localisation')[0] != $roomName || array_column($co2Data, 'localisation')[0] != $roomName) {
                return ['name' => $roomName, 'error' => 'Les données reçues sont nulles'];
            }


            /*
             * Generate the sorted array
             *
             * -> Searching in the three arrays the good data and associate it in the good data type
             *
             * NOTE: This array gets only the DateTime only in temperature array because
             * we consider that every API calls has the same or roughly the same capture date
             */

            //Catching any exception
            try {
                $finalData = array(
                    "name" => $roomName,
                    "lastCapture" => new DateTime(array_column($tempData, 'dateCapture')[0]) ?? null,
                    "temp" => array_column($tempData, 'valeur')[0] ?? null,
                    "hum" => array_column($humData, 'valeur')[0] ?? null,
                    "co2" => array_column($co2Data, 'valeur')[0] ?? null,
                );
            } catch (Exception) { //Catches any exception generated from DateTime convertion for lastCapture stored in array

                return ['name' => $roomName, 'error' => 'Les données sont invalides'];
            }

            return $finalData;
        });
    }
}