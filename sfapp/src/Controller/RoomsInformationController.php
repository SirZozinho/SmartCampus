<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Service\DataCollectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

class RoomsInformationController extends AbstractController
{
    /**
     * @brief Get information of a specific room
     * @param string $name The target room
     * @param RoomRepository $roomRepository The room container
     * @param DataCollectService $collectService The API interface to get external information
     * @return Response returns the view
     * @author Clement MUZELIER-ARTUS
     */
    #[Route('/rooms/{name}', name: 'app_rooms_information')]
    public function index(string $name, RoomRepository $roomRepository, DataCollectService $collectService): Response
    {
        // Search for a room with the name ‘name’.
        $rooms = $roomRepository->findByName($name);

        // Check whether a room has been found, otherwise return a 404 error
        if (empty($rooms)) {
            throw $this->createNotFoundException('La salle n\'existe pas');
        }

        // Recover the room if several results are found
        $room = $rooms[0];

        $latestData = $collectService->getLastCaptures($room->getAcquisitionSystem()->getName(), $room->getName());

        $startDate = new DateTime($latestData["lastCapture"]->format('Y-m-d H:i:s'));
        $startDate->modify('-7 days');

        $chartTemp = $collectService->getAllCapturesBetweenDates($startDate, $latestData["lastCapture"], $roomRepository, $name,'temp');
        $chartHum = $collectService->getAllCapturesBetweenDates($startDate, $latestData["lastCapture"], $roomRepository, $name,'hum');
        $chartCO2 = $collectService->getAllCapturesBetweenDates($startDate, $latestData["lastCapture"], $roomRepository, $name,'co2');

        $notifications = $this->checkConditions($latestData);

        return $this->render('rooms_information/roomsInformation.html.twig', [
            'room' => $room,
            'notifications' => $notifications,
            'data' => $latestData,
            'chartTemp' => $chartTemp,
            'chartHum' => $chartHum,
            'chartCO2' => $chartCO2,
        ]);
    }

    /**
     * @brief Gets notifications in function of received temperature/humidity/co2
     * @param array $latestData The latest data received from API
     * @return array return the list of notifications
     * @author Leonard LARDEUX
     */
    public function checkConditions(array $latestData): array
    {
        // Path to the JSON rules file
        $rulesFilePath = $this->getParameter('kernel.project_dir') . '/assets/json/notification_rules.json';

        // Loading and decoding JSON rules
        if (!file_exists($rulesFilePath)) {
            throw new \RuntimeException('Fichier des règles JSON introuvable');
        }
        $rules = json_decode(file_get_contents($rulesFilePath), true);

        // Initialise notifications
        $notifications = [];

        $currentMonth = (int) date('m');
        $season = ($currentMonth >= 4 && $currentMonth <= 11) ? 'hot_season' : 'cold_season';
        $types = ["temp", "hum", "co2"];

        //Loop all dataTypes in room information
        foreach ($types as $type) {
            //Check if the looped type of data is correctly set
            if (isset($latestData[$type])) {
                //Converts value to a float to be interpreted correctly
                $value = (float) $latestData[$type];

                //Check in function of each advice if it's valid and if it's not, add notification into the list
                foreach ($rules as $rule) {
                    // Comparer le type et vérifier les seuils
                    if ($rule['type'] === $type && ($value < $rule['min'] || $value > $rule['max'])) {
                        if ($season === $rule['season'] || $rule['season'] === 'all') {
                            $notifications[] = sprintf(
                                "%s",
                                $rule['advice']
                            );
                        }
                    }
                }
            }
        }

        return $notifications;
    }
}
