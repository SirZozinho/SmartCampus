<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Service\DataCollectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainMenuController extends AbstractController
{
    /**
     * @author Léonard Lardeux, Louis Paquereau
     * @brief Show the main menu.
     * @param RoomRepository $roomRepository The room repository for fetching room data.
     * @param Request $request The request containing query parameters.
     * @param DataCollectService $collectService Service for collecting external data.
     * @return Response Renders the main menu view.
     */
    #[Route('/', name: 'app_main_menu')]
    public function index(RoomRepository $roomRepository, Request $request, DataCollectService $collectService): Response
    {
        $searchTerm = $request->query->get('name');
        $floorFilter = $request->query->get('floor', 'all');
        $rooms = $this->getFilteredRooms($roomRepository, $searchTerm, $floorFilter);
        $roomsInfos = $this->buildRoomInfos($rooms, $collectService);

        return $this->render('main_menu/main_menu.html.twig', [
            'controller_name' => 'MainMenuController',
            'rooms' => $roomsInfos,
            'searchTerm' => $searchTerm,
            'allFloor' => $roomRepository->getAvailableFloor(),
            'floorFilter' => $floorFilter,
        ]);
    }

    /**
     * @author Léonard Lardeux
     * @brief Get filtered rooms based on search term and floor filter.
     * @param RoomRepository $roomRepository The room repository.
     * @param string|null $searchTerm Search term for filtering rooms by name.
     * @param string $floorFilter Floor filter for narrowing down rooms.
     * @return array List of filtered rooms.
     */
    private function getFilteredRooms(RoomRepository $roomRepository, ?string $searchTerm, string $floorFilter): array
    {
        $rooms = $searchTerm
            ? $roomRepository->findByNameLikeAndState($searchTerm, 'EQUIPÉ')
            : $roomRepository->findByState('EQUIPÉ');

        if ($floorFilter !== 'all') {
            $rooms = array_filter($rooms, function ($room) use ($floorFilter) {
                $roomFloor = $room->getFloor();
                return $floorFilter === 'Rez-de-chaussée' ? $roomFloor === 0 : (int)$floorFilter === $roomFloor;
            });
        }

        return $rooms;
    }



    /**
     * @author Léonard Lardeux, Louis Paquereau
     * @brief Build room information for rendering.
     * @param array $rooms List of rooms.
     * @param DataCollectService $collectService Service for collecting room data.
     * @return array Processed room information.
     */
    private function buildRoomInfos(array $rooms, DataCollectService $collectService): array
    {
        $roomsInfos = [];

        foreach ($rooms as $room) {
            $roomName = $room->getName();
            $roomFloor = $room->getFloor();
            $asName = $room->getAcquisitionsystem()->getName();

            $roomValues = $collectService->getLastCaptures($asName, $roomName);
            $isValuesInRange = $this->checkValuesRange($roomValues);

            $roomsInfos[] = [
                'name' => $roomValues['name'],
                'temp' => $roomValues['temp'] ?? null,
                'hum' => $roomValues['hum'] ?? null,
                'co2' => $roomValues['co2'] ?? null,
                'isTempInRange' => $isValuesInRange['temp'],
                'isHumInRange' => $isValuesInRange['hum'],
                'isCo2InRange' => $isValuesInRange['co2'],
                'floor' => $roomFloor,
            ];
        }

        return $roomsInfos;
    }

    /**
     * @author Léonard Lardeux
     * @brief Check if room variables are within acceptable ranges.
     * @param array $latestData Data received from the API.
     * @return bool[] Status of each variable (in range or not).
     */
    private function checkValuesRange(array $latestData): array
    {
        $ranges = [
            'temp' => ['min' => 17, 'max' => 25],
            'hum' => ['min' => 40, 'max' => 70],
            'co2' => ['min' => 0, 'max' => 1500],
        ];

        $results = [];
        foreach ($ranges as $key => $range) {
            $value = $latestData[$key] ?? null;
            $results[$key] = $value !== null && $value >= $range['min'] && $value <= $range['max'];
        }

        return $results;
    }
}
