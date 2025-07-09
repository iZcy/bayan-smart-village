<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StuntingCalculatorController extends Controller
{
    public function index()
    {
        return Inertia::render('StuntingCalculator', [
            'whoData' => [
                'boys' => $this->getBoysData(),
                'girls' => $this->getGirlsData(),
            ]
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:boys,girls',
            'height' => 'required|numeric|min:10|max:200',
            'birth_date' => 'required|date|before:today',
        ]);

        $birthDate = Carbon::parse($request->birth_date);
        $today = Carbon::today();

        // Calculate age in months according to the rule
        $ageInMonths = $this->calculateAgeInMonths($birthDate, $today);

        // Get WHO data for the specific gender and age
        $whoData = $request->gender === 'boys' ? $this->getBoysData() : $this->getGirlsData();

        if (!isset($whoData[$ageInMonths])) {
            return response()->json([
                'error' => 'Age out of range. Calculator works for children 0-60 months old.'
            ], 400);
        }

        $standards = $whoData[$ageInMonths];
        $actualHeight = $request->height;
        $median = $standards['median'];

        // Calculate Height-for-Age Z-score (HAZ)
        if ($actualHeight >= $median) {
            // Formula 1: if height >= median
            $haz = ($actualHeight - $median) / ($standards['1sd'] - $median);
        } else {
            // Formula 2: if height < median
            $haz = ($actualHeight - $median) / ($median - $standards['-1sd']);
        }

        // Determine stunting status
        $status = $this->determineStuntingStatus($haz);

        return response()->json([
            'age_months' => $ageInMonths,
            'height' => $actualHeight,
            'median_height' => $median,
            'haz_score' => round($haz, 2),
            'status' => $status,
            'standards' => $standards,
            'interpretation' => $this->getInterpretation($haz, $status)
        ]);
    }

    private function calculateAgeInMonths(Carbon $birthDate, Carbon $today): int
    {
        // If birth day is > 15, consider it as next month
        if ($birthDate->day > 15) {
            $birthDate = $birthDate->copy()->addMonth()->startOfMonth();
        } else {
            $birthDate = $birthDate->copy()->startOfMonth();
        }

        $today = $today->copy()->startOfMonth();

        return $birthDate->diffInMonths($today);
    }

    private function determineStuntingStatus(float $haz): string
    {
        if ($haz < -3) {
            return 'severely_stunted';
        } elseif ($haz < -2) {
            return 'stunted';
        } elseif ($haz >= -2 && $haz <= 2) {
            return 'normal';
        } else {
            return 'tall';
        }
    }

    private function getInterpretation(float $haz, string $status): array
    {
        $interpretations = [
            'severely_stunted' => [
                'title' => 'Severely Stunted',
                'description' => 'Height-for-age is below -3 SD. This indicates severe chronic malnutrition.',
                'recommendation' => 'Immediate medical attention and nutritional intervention required.',
                'color' => 'red'
            ],
            'stunted' => [
                'title' => 'Stunted',
                'description' => 'Height-for-age is below -2 SD. This indicates chronic malnutrition.',
                'recommendation' => 'Nutritional counseling and regular monitoring recommended.',
                'color' => 'orange'
            ],
            'normal' => [
                'title' => 'Normal',
                'description' => 'Height-for-age is within normal range (-2 to +2 SD).',
                'recommendation' => 'Continue current feeding practices and regular health check-ups.',
                'color' => 'green'
            ],
            'tall' => [
                'title' => 'Tall',
                'description' => 'Height-for-age is above +2 SD. Child is taller than average.',
                'recommendation' => 'Monitor growth patterns and ensure balanced nutrition.',
                'color' => 'blue'
            ]
        ];

        return $interpretations[$status];
    }

    private function getBoysData(): array
    {
        // WHO Growth Standards for Boys (Height-for-Age in cm)
        // Data for months 0-60, columns: -3SD, -2SD, -1SD, Median, 1SD, 2SD, 3SD
        return [
            0 => ['-3sd' => 44.2, '-2sd' => 46.1, '-1sd' => 48.0, 'median' => 49.9, '1sd' => 51.8, '2sd' => 53.7, '3sd' => 55.6],
            1 => ['-3sd' => 48.9, '-2sd' => 50.8, '-1sd' => 52.8, 'median' => 54.7, '1sd' => 56.7, '2sd' => 58.6, '3sd' => 60.6],
            2 => ['-3sd' => 52.4, '-2sd' => 54.4, '-1sd' => 56.4, 'median' => 58.4, '1sd' => 60.4, '2sd' => 62.4, '3sd' => 64.4],
            3 => ['-3sd' => 55.3, '-2sd' => 57.3, '-1sd' => 59.4, 'median' => 61.4, '1sd' => 63.5, '2sd' => 65.5, '3sd' => 67.6],
            4 => ['-3sd' => 57.6, '-2sd' => 59.7, '-1sd' => 61.8, 'median' => 63.9, '1sd' => 66.0, '2sd' => 68.0, '3sd' => 70.1],
            5 => ['-3sd' => 59.6, '-2sd' => 61.7, '-1sd' => 63.8, 'median' => 65.9, '1sd' => 68.0, '2sd' => 70.1, '3sd' => 72.2],
            6 => ['-3sd' => 61.2, '-2sd' => 63.3, '-1sd' => 65.5, 'median' => 67.6, '1sd' => 69.8, '2sd' => 71.9, '3sd' => 74.0],
            7 => ['-3sd' => 62.7, '-2sd' => 64.8, '-1sd' => 67.0, 'median' => 69.2, '1sd' => 71.3, '2sd' => 73.5, '3sd' => 75.7],
            8 => ['-3sd' => 64.0, '-2sd' => 66.2, '-1sd' => 68.4, 'median' => 70.6, '1sd' => 72.8, '2sd' => 75.0, '3sd' => 77.2],
            9 => ['-3sd' => 65.2, '-2sd' => 67.5, '-1sd' => 69.7, 'median' => 72.0, '1sd' => 74.2, '2sd' => 76.5, '3sd' => 78.7],
            10 => ['-3sd' => 66.4, '-2sd' => 68.7, '-1sd' => 71.0, 'median' => 73.3, '1sd' => 75.6, '2sd' => 77.9, '3sd' => 80.1],
            11 => ['-3sd' => 67.6, '-2sd' => 69.9, '-1sd' => 72.2, 'median' => 74.5, '1sd' => 76.9, '2sd' => 79.2, '3sd' => 81.5],
            12 => ['-3sd' => 68.6, '-2sd' => 71.0, '-1sd' => 73.4, 'median' => 75.7, '1sd' => 78.1, '2sd' => 80.5, '3sd' => 82.9],
            13 => ['-3sd' => 69.6, '-2sd' => 72.1, '-1sd' => 74.5, 'median' => 76.9, '1sd' => 79.3, '2sd' => 81.8, '3sd' => 84.2],
            14 => ['-3sd' => 70.6, '-2sd' => 73.1, '-1sd' => 75.6, 'median' => 78.0, '1sd' => 80.5, '2sd' => 83.0, '3sd' => 85.5],
            15 => ['-3sd' => 71.6, '-2sd' => 74.1, '-1sd' => 76.6, 'median' => 79.1, '1sd' => 81.7, '2sd' => 84.2, '3sd' => 86.7],
            16 => ['-3sd' => 72.5, '-2sd' => 75.0, '-1sd' => 77.6, 'median' => 80.2, '1sd' => 82.8, '2sd' => 85.4, '3sd' => 88.0],
            17 => ['-3sd' => 73.3, '-2sd' => 76.0, '-1sd' => 78.6, 'median' => 81.2, '1sd' => 83.9, '2sd' => 86.5, '3sd' => 89.2],
            18 => ['-3sd' => 74.2, '-2sd' => 76.9, '-1sd' => 79.6, 'median' => 82.3, '1sd' => 85.0, '2sd' => 87.7, '3sd' => 90.4],
            19 => ['-3sd' => 75.0, '-2sd' => 77.7, '-1sd' => 80.5, 'median' => 83.2, '1sd' => 86.0, '2sd' => 88.8, '3sd' => 91.5],
            20 => ['-3sd' => 75.8, '-2sd' => 78.6, '-1sd' => 81.4, 'median' => 84.2, '1sd' => 87.0, '2sd' => 89.8, '3sd' => 92.6],
            21 => ['-3sd' => 76.5, '-2sd' => 79.4, '-1sd' => 82.3, 'median' => 85.1, '1sd' => 88.0, '2sd' => 90.9, '3sd' => 93.8],
            22 => ['-3sd' => 77.2, '-2sd' => 80.2, '-1sd' => 83.1, 'median' => 86.0, '1sd' => 88.9, '2sd' => 91.9, '3sd' => 94.9],
            23 => ['-3sd' => 78.0, '-2sd' => 81.0, '-1sd' => 83.9, 'median' => 86.9, '1sd' => 89.9, '2sd' => 92.9, '3sd' => 95.9],
            24 => ['-3sd' => 78.7, '-2sd' => 81.7, '-1sd' => 84.8, 'median' => 87.8, '1sd' => 90.9, '2sd' => 93.9, '3sd' => 97.0],
            25 => ['-3sd' => 79.3, '-2sd' => 82.5, '-1sd' => 85.6, 'median' => 88.7, '1sd' => 91.8, '2sd' => 94.9, '3sd' => 98.0],
            26 => ['-3sd' => 80.0, '-2sd' => 83.2, '-1sd' => 86.4, 'median' => 89.6, '1sd' => 92.7, '2sd' => 95.9, '3sd' => 99.0],
            27 => ['-3sd' => 80.7, '-2sd' => 83.9, '-1sd' => 87.2, 'median' => 90.4, '1sd' => 93.6, '2sd' => 96.8, '3sd' => 100.0],
            28 => ['-3sd' => 81.3, '-2sd' => 84.6, '-1sd' => 87.9, 'median' => 91.2, '1sd' => 94.5, '2sd' => 97.8, '3sd' => 101.0],
            29 => ['-3sd' => 81.9, '-2sd' => 85.3, '-1sd' => 88.7, 'median' => 92.0, '1sd' => 95.4, '2sd' => 98.7, '3sd' => 102.0],
            30 => ['-3sd' => 82.5, '-2sd' => 86.0, '-1sd' => 89.4, 'median' => 92.8, '1sd' => 96.2, '2sd' => 99.6, '3sd' => 103.0],
            31 => ['-3sd' => 83.1, '-2sd' => 86.7, '-1sd' => 90.2, 'median' => 93.6, '1sd' => 97.0, '2sd' => 100.5, '3sd' => 103.9],
            32 => ['-3sd' => 83.6, '-2sd' => 87.3, '-1sd' => 90.9, 'median' => 94.4, '1sd' => 97.8, '2sd' => 101.3, '3sd' => 104.8],
            33 => ['-3sd' => 84.2, '-2sd' => 87.9, '-1sd' => 91.6, 'median' => 95.1, '1sd' => 98.6, '2sd' => 102.2, '3sd' => 105.7],
            34 => ['-3sd' => 84.7, '-2sd' => 88.5, '-1sd' => 92.2, 'median' => 95.9, '1sd' => 99.4, '2sd' => 103.0, '3sd' => 106.6],
            35 => ['-3sd' => 85.2, '-2sd' => 89.1, '-1sd' => 92.9, 'median' => 96.6, '1sd' => 100.2, '2sd' => 103.8, '3sd' => 107.5],
            36 => ['-3sd' => 85.7, '-2sd' => 89.6, '-1sd' => 93.5, 'median' => 97.3, '1sd' => 101.0, '2sd' => 104.6, '3sd' => 108.3],
            37 => ['-3sd' => 86.2, '-2sd' => 90.2, '-1sd' => 94.1, 'median' => 98.0, '1sd' => 101.7, '2sd' => 105.4, '3sd' => 109.1],
            38 => ['-3sd' => 86.7, '-2sd' => 90.7, '-1sd' => 94.7, 'median' => 98.6, '1sd' => 102.4, '2sd' => 106.2, '3sd' => 109.9],
            39 => ['-3sd' => 87.1, '-2sd' => 91.2, '-1sd' => 95.3, 'median' => 99.3, '1sd' => 103.1, '2sd' => 106.9, '3sd' => 110.7],
            40 => ['-3sd' => 87.5, '-2sd' => 91.7, '-1sd' => 95.9, 'median' => 99.9, '1sd' => 103.8, '2sd' => 107.7, '3sd' => 111.5],
            41 => ['-3sd' => 88.0, '-2sd' => 92.2, '-1sd' => 96.4, 'median' => 100.4, '1sd' => 104.5, '2sd' => 108.4, '3sd' => 112.2],
            42 => ['-3sd' => 88.4, '-2sd' => 92.7, '-1sd' => 96.9, 'median' => 101.0, '1sd' => 105.1, '2sd' => 109.1, '3sd' => 112.9],
            43 => ['-3sd' => 88.8, '-2sd' => 93.1, '-1sd' => 97.4, 'median' => 101.6, '1sd' => 105.8, '2sd' => 109.8, '3sd' => 113.6],
            44 => ['-3sd' => 89.2, '-2sd' => 93.6, '-1sd' => 97.9, 'median' => 102.1, '1sd' => 106.4, '2sd' => 110.5, '3sd' => 114.3],
            45 => ['-3sd' => 89.6, '-2sd' => 94.0, '-1sd' => 98.4, 'median' => 102.6, '1sd' => 107.0, '2sd' => 111.1, '3sd' => 115.0],
            46 => ['-3sd' => 89.9, '-2sd' => 94.4, '-1sd' => 98.9, 'median' => 103.1, '1sd' => 107.6, '2sd' => 111.8, '3sd' => 115.7],
            47 => ['-3sd' => 90.3, '-2sd' => 94.8, '-1sd' => 99.3, 'median' => 103.6, '1sd' => 108.1, '2sd' => 112.4, '3sd' => 116.3],
            48 => ['-3sd' => 90.6, '-2sd' => 95.2, '-1sd' => 99.7, 'median' => 104.1, '1sd' => 108.6, '2sd' => 113.0, '3sd' => 116.9],
            49 => ['-3sd' => 90.9, '-2sd' => 95.6, '-1sd' => 100.1, 'median' => 104.6, '1sd' => 109.1, '2sd' => 113.6, '3sd' => 117.5],
            50 => ['-3sd' => 91.2, '-2sd' => 95.9, '-1sd' => 100.5, 'median' => 105.0, '1sd' => 109.6, '2sd' => 114.1, '3sd' => 118.1],
            51 => ['-3sd' => 91.5, '-2sd' => 96.3, '-1sd' => 100.9, 'median' => 105.4, '1sd' => 110.1, '2sd' => 114.7, '3sd' => 118.7],
            52 => ['-3sd' => 91.8, '-2sd' => 96.6, '-1sd' => 101.2, 'median' => 105.8, '1sd' => 110.5, '2sd' => 115.2, '3sd' => 119.2],
            53 => ['-3sd' => 92.1, '-2sd' => 96.9, '-1sd' => 101.6, 'median' => 106.2, '1sd' => 110.9, '2sd' => 115.7, '3sd' => 119.8],
            54 => ['-3sd' => 92.4, '-2sd' => 97.2, '-1sd' => 101.9, 'median' => 106.6, '1sd' => 111.3, '2sd' => 116.2, '3sd' => 120.3],
            55 => ['-3sd' => 92.6, '-2sd' => 97.5, '-1sd' => 102.2, 'median' => 106.9, '1sd' => 111.7, '2sd' => 116.7, '3sd' => 120.8],
            56 => ['-3sd' => 92.9, '-2sd' => 97.8, '-1sd' => 102.5, 'median' => 107.2, '1sd' => 112.1, '2sd' => 117.1, '3sd' => 121.3],
            57 => ['-3sd' => 93.1, '-2sd' => 98.1, '-1sd' => 102.8, 'median' => 107.5, '1sd' => 112.4, '2sd' => 117.6, '3sd' => 121.8],
            58 => ['-3sd' => 93.4, '-2sd' => 98.3, '-1sd' => 103.1, 'median' => 107.8, '1sd' => 112.7, '2sd' => 118.0, '3sd' => 122.2],
            59 => ['-3sd' => 93.6, '-2sd' => 98.6, '-1sd' => 103.3, 'median' => 108.1, '1sd' => 113.0, '2sd' => 118.4, '3sd' => 122.7],
            60 => ['-3sd' => 93.8, '-2sd' => 98.8, '-1sd' => 103.6, 'median' => 108.4, '1sd' => 113.3, '2sd' => 118.8, '3sd' => 123.1],
        ];
    }

    private function getGirlsData(): array
    {
        // WHO Growth Standards for Girls (Height-for-Age in cm)
        return [
            0 => ['-3sd' => 43.6, '-2sd' => 45.4, '-1sd' => 47.3, 'median' => 49.1, '1sd' => 51.0, '2sd' => 52.9, '3sd' => 54.7],
            1 => ['-3sd' => 47.8, '-2sd' => 49.8, '-1sd' => 51.7, 'median' => 53.7, '1sd' => 55.6, '2sd' => 57.6, '3sd' => 59.5],
            2 => ['-3sd' => 51.0, '-2sd' => 53.0, '-1sd' => 55.0, 'median' => 57.1, '1sd' => 59.1, '2sd' => 61.1, '3sd' => 63.2],
            3 => ['-3sd' => 53.5, '-2sd' => 55.6, '-1sd' => 57.7, 'median' => 59.8, '1sd' => 61.9, '2sd' => 64.0, '3sd' => 66.1],
            4 => ['-3sd' => 55.6, '-2sd' => 57.8, '-1sd' => 59.9, 'median' => 62.1, '1sd' => 64.3, '2sd' => 66.4, '3sd' => 68.6],
            5 => ['-3sd' => 57.4, '-2sd' => 59.6, '-1sd' => 61.8, 'median' => 64.0, '1sd' => 66.2, '2sd' => 68.5, '3sd' => 70.7],
            6 => ['-3sd' => 58.9, '-2sd' => 61.2, '-1sd' => 63.5, 'median' => 65.7, '1sd' => 68.0, '2sd' => 70.3, '3sd' => 72.5],
            7 => ['-3sd' => 60.3, '-2sd' => 62.7, '-1sd' => 65.0, 'median' => 67.3, '1sd' => 69.6, '2sd' => 71.9, '3sd' => 74.2],
            8 => ['-3sd' => 61.7, '-2sd' => 64.0, '-1sd' => 66.4, 'median' => 68.7, '1sd' => 71.1, '2sd' => 73.5, '3sd' => 75.8],
            9 => ['-3sd' => 62.9, '-2sd' => 65.3, '-1sd' => 67.7, 'median' => 70.1, '1sd' => 72.6, '2sd' => 75.0, '3sd' => 77.4],
            10 => ['-3sd' => 64.1, '-2sd' => 66.5, '-1sd' => 69.0, 'median' => 71.5, '1sd' => 73.9, '2sd' => 76.4, '3sd' => 78.9],
            11 => ['-3sd' => 65.2, '-2sd' => 67.7, '-1sd' => 70.3, 'median' => 72.8, '1sd' => 75.3, '2sd' => 77.8, '3sd' => 80.3],
            12 => ['-3sd' => 66.3, '-2sd' => 68.9, '-1sd' => 71.4, 'median' => 74.0, '1sd' => 76.6, '2sd' => 79.2, '3sd' => 81.7],
            13 => ['-3sd' => 67.3, '-2sd' => 70.0, '-1sd' => 72.6, 'median' => 75.2, '1sd' => 77.8, '2sd' => 80.5, '3sd' => 83.1],
            14 => ['-3sd' => 68.3, '-2sd' => 71.0, '-1sd' => 73.7, 'median' => 76.4, '1sd' => 79.1, '2sd' => 81.7, '3sd' => 84.4],
            15 => ['-3sd' => 69.3, '-2sd' => 72.0, '-1sd' => 74.8, 'median' => 77.5, '1sd' => 80.2, '2sd' => 83.0, '3sd' => 85.7],
            16 => ['-3sd' => 70.2, '-2sd' => 73.0, '-1sd' => 75.8, 'median' => 78.6, '1sd' => 81.4, '2sd' => 84.2, '3sd' => 87.0],
            17 => ['-3sd' => 71.1, '-2sd' => 74.0, '-1sd' => 76.8, 'median' => 79.7, '1sd' => 82.5, '2sd' => 85.4, '3sd' => 88.2],
            18 => ['-3sd' => 72.0, '-2sd' => 74.9, '-1sd' => 77.8, 'median' => 80.7, '1sd' => 83.6, '2sd' => 86.5, '3sd' => 89.4],
            19 => ['-3sd' => 72.8, '-2sd' => 75.8, '-1sd' => 78.8, 'median' => 81.7, '1sd' => 84.7, '2sd' => 87.6, '3sd' => 90.6],
            20 => ['-3sd' => 73.7, '-2sd' => 76.7, '-1sd' => 79.7, 'median' => 82.7, '1sd' => 85.7, '2sd' => 88.7, '3sd' => 91.7],
            21 => ['-3sd' => 74.5, '-2sd' => 77.5, '-1sd' => 80.6, 'median' => 83.7, '1sd' => 86.7, '2sd' => 89.8, '3sd' => 92.9],
            22 => ['-3sd' => 75.2, '-2sd' => 78.4, '-1sd' => 81.5, 'median' => 84.6, '1sd' => 87.7, '2sd' => 90.8, '3sd' => 94.0],
            23 => ['-3sd' => 76.0, '-2sd' => 79.2, '-1sd' => 82.3, 'median' => 85.5, '1sd' => 88.7, '2sd' => 91.9, '3sd' => 95.0],
            24 => ['-3sd' => 76.7, '-2sd' => 80.0, '-1sd' => 83.2, 'median' => 86.4, '1sd' => 89.6, '2sd' => 92.9, '3sd' => 96.1],
            25 => ['-3sd' => 77.5, '-2sd' => 80.8, '-1sd' => 84.0, 'median' => 87.3, '1sd' => 90.6, '2sd' => 93.8, '3sd' => 97.1],
            26 => ['-3sd' => 78.2, '-2sd' => 81.5, '-1sd' => 84.8, 'median' => 88.1, '1sd' => 91.4, '2sd' => 94.7, '3sd' => 98.1],
            27 => ['-3sd' => 78.9, '-2sd' => 82.3, '-1sd' => 85.6, 'median' => 88.9, '1sd' => 92.3, '2sd' => 95.6, '3sd' => 99.0],
            28 => ['-3sd' => 79.6, '-2sd' => 83.0, '-1sd' => 86.4, 'median' => 89.7, '1sd' => 93.1, '2sd' => 96.5, '3sd' => 99.9],
            29 => ['-3sd' => 80.2, '-2sd' => 83.7, '-1sd' => 87.1, 'median' => 90.5, '1sd' => 93.9, '2sd' => 97.3, '3sd' => 100.7],
            30 => ['-3sd' => 80.9, '-2sd' => 84.4, '-1sd' => 87.9, 'median' => 91.3, '1sd' => 94.7, '2sd' => 98.1, '3sd' => 101.5],
            31 => ['-3sd' => 81.5, '-2sd' => 85.1, '-1sd' => 88.6, 'median' => 92.0, '1sd' => 95.4, '2sd' => 98.9, '3sd' => 102.3],
            32 => ['-3sd' => 82.1, '-2sd' => 85.7, '-1sd' => 89.3, 'median' => 92.8, '1sd' => 96.2, '2sd' => 99.7, '3sd' => 103.1],
            33 => ['-3sd' => 82.7, '-2sd' => 86.4, '-1sd' => 90.0, 'median' => 93.5, '1sd' => 96.9, '2sd' => 100.4, '3sd' => 103.9],
            34 => ['-3sd' => 83.3, '-2sd' => 87.0, '-1sd' => 90.7, 'median' => 94.2, '1sd' => 97.6, '2sd' => 101.1, '3sd' => 104.6],
            35 => ['-3sd' => 83.9, '-2sd' => 87.6, '-1sd' => 91.3, 'median' => 94.9, '1sd' => 98.4, '2sd' => 101.9, '3sd' => 105.3],
            36 => ['-3sd' => 84.4, '-2sd' => 88.2, '-1sd' => 92.0, 'median' => 95.6, '1sd' => 99.1, '2sd' => 102.6, '3sd' => 106.0],
            37 => ['-3sd' => 85.0, '-2sd' => 88.8, '-1sd' => 92.6, 'median' => 96.2, '1sd' => 99.8, '2sd' => 103.3, '3sd' => 106.7],
            38 => ['-3sd' => 85.5, '-2sd' => 89.3, '-1sd' => 93.2, 'median' => 96.9, '1sd' => 100.4, '2sd' => 103.9, '3sd' => 107.4],
            39 => ['-3sd' => 86.0, '-2sd' => 89.9, '-1sd' => 93.8, 'median' => 97.5, '1sd' => 101.1, '2sd' => 104.6, '3sd' => 108.0],
            40 => ['-3sd' => 86.5, '-2sd' => 90.4, '-1sd' => 94.4, 'median' => 98.1, '1sd' => 101.7, '2sd' => 105.2, '3sd' => 108.6],
            41 => ['-3sd' => 87.0, '-2sd' => 90.9, '-1sd' => 94.9, 'median' => 98.7, '1sd' => 102.3, '2sd' => 105.8, '3sd' => 109.2],
            42 => ['-3sd' => 87.5, '-2sd' => 91.4, '-1sd' => 95.5, 'median' => 99.3, '1sd' => 102.9, '2sd' => 106.4, '3sd' => 109.8],
            43 => ['-3sd' => 87.9, '-2sd' => 91.9, '-1sd' => 96.0, 'median' => 99.8, '1sd' => 103.5, '2sd' => 107.0, '3sd' => 110.4],
            44 => ['-3sd' => 88.4, '-2sd' => 92.4, '-1sd' => 96.5, 'median' => 100.3, '1sd' => 104.0, '2sd' => 107.5, '3sd' => 110.9],
            45 => ['-3sd' => 88.8, '-2sd' => 92.9, '-1sd' => 97.0, 'median' => 100.8, '1sd' => 104.5, '2sd' => 108.1, '3sd' => 111.5],
            46 => ['-3sd' => 89.3, '-2sd' => 93.3, '-1sd' => 97.5, 'median' => 101.3, '1sd' => 105.0, '2sd' => 108.6, '3sd' => 112.0],
            47 => ['-3sd' => 89.7, '-2sd' => 93.8, '-1sd' => 97.9, 'median' => 101.8, '1sd' => 105.5, '2sd' => 109.1, '3sd' => 112.5],
            48 => ['-3sd' => 90.1, '-2sd' => 94.2, '-1sd' => 98.4, 'median' => 102.3, '1sd' => 106.0, '2sd' => 109.6, '3sd' => 113.0],
            49 => ['-3sd' => 90.5, '-2sd' => 94.6, '-1sd' => 98.8, 'median' => 102.7, '1sd' => 106.4, '2sd' => 110.1, '3sd' => 113.5],
            50 => ['-3sd' => 90.9, '-2sd' => 95.0, '-1sd' => 99.2, 'median' => 103.1, '1sd' => 106.9, '2sd' => 110.5, '3sd' => 113.9],
            51 => ['-3sd' => 91.2, '-2sd' => 95.4, '-1sd' => 99.6, 'median' => 103.5, '1sd' => 107.3, '2sd' => 110.9, '3sd' => 114.4],
            52 => ['-3sd' => 91.6, '-2sd' => 95.8, '-1sd' => 100.0, 'median' => 103.9, '1sd' => 107.7, '2sd' => 111.4, '3sd' => 114.8],
            53 => ['-3sd' => 91.9, '-2sd' => 96.1, '-1sd' => 100.4, 'median' => 104.3, '1sd' => 108.1, '2sd' => 111.8, '3sd' => 115.2],
            54 => ['-3sd' => 92.3, '-2sd' => 96.5, '-1sd' => 100.7, 'median' => 104.6, '1sd' => 108.4, '2sd' => 112.2, '3sd' => 115.6],
            55 => ['-3sd' => 92.6, '-2sd' => 96.8, '-1sd' => 101.0, 'median' => 105.0, '1sd' => 108.8, '2sd' => 112.5, '3sd' => 116.0],
            56 => ['-3sd' => 92.9, '-2sd' => 97.1, '-1sd' => 101.3, 'median' => 105.3, '1sd' => 109.1, '2sd' => 112.9, '3sd' => 116.3],
            57 => ['-3sd' => 93.2, '-2sd' => 97.4, '-1sd' => 101.6, 'median' => 105.6, '1sd' => 109.4, '2sd' => 113.2, '3sd' => 116.7],
            58 => ['-3sd' => 93.5, '-2sd' => 97.7, '-1sd' => 101.9, 'median' => 105.9, '1sd' => 109.7, '2sd' => 113.5, '3sd' => 117.0],
            59 => ['-3sd' => 93.8, '-2sd' => 98.0, '-1sd' => 102.2, 'median' => 106.2, '1sd' => 110.0, '2sd' => 113.8, '3sd' => 117.3],
            60 => ['-3sd' => 94.1, '-2sd' => 98.2, '-1sd' => 102.4, 'median' => 106.5, '1sd' => 110.3, '2sd' => 114.1, '3sd' => 117.6],
        ];
    }
}
