<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Service\DataCollectService;
use DateMalformedStringException;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatistiquesController extends AbstractController
{
    /**
     * @brief show stats
     * @param Request $request every params (selected month, selected interval for each type of value)
     * @param DataCollectService $service The API Interface to collect external information
     * @param RoomRepository $roomRepository The rooms container
     * @return Response returns the view
     * @throws DateMalformedStringException
     * @author Enzo BIGUET, Louis PAQUEREAU
     */
    #[Route('/stats', name: 'app_statistiques')]
    public function index(Request $request, DataCollectService $service, RoomRepository $roomRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $selectedMonth = (int) $request->query->get('month', 12);
        $fromDate = new DateTime("first day of -1 year January"); //Define the first day of january of last year
        $fromDate->setDate($fromDate->format('Y'), $selectedMonth, 1); //Defines the start date with the selected month
        $toDate = (clone $fromDate)->modify('+1 month');//Defines the end day
        $data = $service->getCapturesBetweenDates($fromDate, $toDate, $roomRepository); //Defines all data received from API
        //dump($data);

        // Defines default filters
        $minTemp = (float) $request->query->get('minValue', 17);
        $maxTemp = (float) $request->query->get('maxValue', 21);
        $minHum = (float) $request->query->get('minHumidity', 0);
        $maxHum = (float) $request->query->get('maxHumidity', 70);
        $minCo2 = (float) $request->query->get('minCo2', 440);
        $maxCo2 = (float) $request->query->get('maxCo2', 1000);


        //Separates temp data from data received from api
        $tempLineData = []; //Defines API data formatted for temp graph
        foreach ($data['days'] as $day) {
            foreach ($data['rooms'] as $room => $roomData) {
                //Checks if data exists
                if (isset($roomData['temp'][$day ])) {
                    //Checks if temp date is between temperature filters
                    if ($roomData['temp'][$day] >= $minTemp && $roomData['temp'][$day] <= $maxTemp) {
                        $tempLineData[$day][] = ['x' => $day, 'y' => $roomData['temp'][$day], 'label' => $room];
                    }
                }
            }
        }

        $humLineData = []; //Defines API data formatted for humidity graph
        foreach ($data['days'] as $day) {
            foreach ($data['rooms'] as $roomName => $roomData) {

                //Checks if data exist
                if (isset($roomData['hum'][$day])) {
                    //Checks if hum is between humidity filters
                    if ($roomData['hum'][$day] < $minHum || $roomData['hum'][$day] > $maxHum) {
                        $humLineData[$day][] = [
                            'x' => $day,
                            'y' => $roomData['hum'][$day],
                            'label' => $roomName,
                        ];
                    }
                }
            }
        }



        $co2LineData = []; //Defines API data formatted for co2 graph
        foreach ($data['days'] as $day) {
            foreach ($data['rooms'] as $room => $roomData) {
                //Checks if data exists
                if (isset($roomData['co2'][$day ])) {
                    //Checks if data is between co2 filters
                    if ($roomData['co2'][$day] >= $minCo2 && $roomData['co2'][$day] <= $maxCo2) {
                        $co2LineData[$day][] = ['x' => $day, 'y' => $roomData['co2'][$day], 'label' => $room];
                    }
                }
            }
        }

        $chartData = [
            'chart2' => [
                'title' => "Nombre de salles en dehors de la plage de température (°C)",
                'type' => 'line',
                'labels' => $data['days'],
                'data' => array_map(fn($dayScatterData) => count($dayScatterData), $tempLineData),
            ],
            'chart3' => [
                'title' => "Nombre de salles en dehors de la plage d humidité (%)",
                'type' => 'line',
                'labels' => $data['days'],
                'data' => array_map(fn($dayScatterData) => count($dayScatterData), $humLineData),
            ],
            'chart4' => [
                'title' => "Nombre de salles en dehors de la plage du taux de CO2 (PPM)",
                'type' => 'line',
                'labels' => $data['days'],
                'data' => array_map(fn($dayScatterData) => count($dayScatterData), $co2LineData),
            ],
        ];

        return $this->render('stats/stats.html.twig', [
            'chartData' => $chartData,
            'minTemp' => $minTemp,
            'maxTemp' => $maxTemp,
            'minHum' => $minHum,
            'maxHum' => $maxHum,
            'minCo2' => $minCo2,
            'maxCo2' => $maxCo2,
            'selectedMonth' => $selectedMonth,
        ]);
    }
}
