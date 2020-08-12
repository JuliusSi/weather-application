<?php

namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Src\Weather\Client\Request\DefaultRequest;
use Src\Weather\Client\Response\ForecastTimestamp;
use Src\Weather\Client\Response\Response;
use Src\Weather\Repository\WeatherRepository;
use DateTimeZone;
use Illuminate\Support\Carbon;

/**
 * Class WeatherForBasketBallService
 * @package App\Service
 */
class WeatherForBasketBallService
{
    private const HOURS_TO_CHECK = 4;

    /**
     * @var WeatherRepository
     */
    private WeatherRepository $weatherRepository;

    public function __construct(WeatherRepository $weatherRepository)
    {
        $this->weatherRepository = $weatherRepository;
    }

    /**
     * @return ForecastTimestamp[]
     */
    public function getFilteredWeatherInformation(): array
    {
        $response = $this->getWeatherInformation();
        if (!$response) {
            return [];
        }

        return $this->filterWeatherInformation($response);
    }

    /**
     * @param  Response  $response
     * @return array
     */
    private function filterWeatherInformation(Response $response): array
    {
        $dateToCheck = $this->getDateTimeToCheck();
        $datetime = Carbon::now()->toDateTimeString();

        $weatherInformationArray = [];
        foreach ($response->getForecastTimestamps() as $key => $forecastTimestamp) {
            if (count($weatherInformationArray) === self::HOURS_TO_CHECK) {
                return $weatherInformationArray;
            }
            if ($this->isValidForecastTimeStamp($forecastTimestamp, $dateToCheck, $datetime)) {
                $weatherInformationArray[] = $forecastTimestamp;
            }
        }

        return $weatherInformationArray;
    }

    /**
     * @return string
     */
    private function getDateTimeToCheck(): string
    {
        return Carbon::now()->addHours(self::HOURS_TO_CHECK)
            ->setTimezone(new DateTimeZone('Europe/Vilnius'))->toDateTimeString();
    }

    /**
     * @param  ForecastTimestamp  $forecastTimeStamp
     * @param  string  $datetimeAfterForHours
     * @param  string  $dateTime
     * @return bool
     */
    private function isValidForecastTimeStamp(
        ForecastTimestamp $forecastTimeStamp,
        string $datetimeAfterForHours,
        string $dateTime
    ): bool {
        $forecastTimestampUtc = $forecastTimeStamp->getForecastTimeUtc();

        return $datetimeAfterForHours > $forecastTimestampUtc &&
            $forecastTimestampUtc > $dateTime;
    }

    /**
     * @return Response
     */
    private function getWeatherInformation(): ?Response
    {
        try {
            return $this->weatherRepository->find($this->buildRequest());
        } catch (GuzzleException $exception) {
            Log::warning('Can not get response from meteo.');

            return null;
        }
    }

    /**
     * @return DefaultRequest
     */
    private function buildRequest(): DefaultRequest
    {
        $request = new DefaultRequest();
        $request->setCity('Vilnius');

        return $request;
    }
}
