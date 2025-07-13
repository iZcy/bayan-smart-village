<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StuntingCalculatorController extends Controller
{
    private $boysData = null;
    private $girlsData = null;

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
        ], [
            // Indonesian validation messages
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'height.required' => 'Tinggi badan wajib diisi.',
            'height.numeric' => 'Tinggi badan harus berupa angka.',
            'height.min' => 'Tinggi badan minimal 10 cm.',
            'height.max' => 'Tinggi badan maksimal 200 cm.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        $birthDate = Carbon::parse($request->birth_date);
        $today = Carbon::today();

        // Calculate age in months according to the rule
        $ageInMonths = $this->calculateAgeInMonths($birthDate, $today);

        // Get WHO data for the specific gender and age
        $whoData = $request->gender === 'boys' ? $this->getBoysData() : $this->getGirlsData();

        if (!isset($whoData[$ageInMonths])) {
            return response()->json([
                'error' => 'Usia di luar rentang. Kalkulator ini bekerja untuk anak usia 0-60 bulan.'
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
                'title' => 'Stunting Berat',
                'description' => 'Tinggi badan menurut umur berada di bawah -3 SD. Ini menunjukkan malnutrisi kronik yang parah.',
                'recommendation' => 'Diperlukan perhatian medis segera dan intervensi gizi yang intensif.',
                'color' => 'red'
            ],
            'stunted' => [
                'title' => 'Stunting',
                'description' => 'Tinggi badan menurut umur berada di bawah -2 SD. Ini menunjukkan malnutrisi kronik.',
                'recommendation' => 'Disarankan konseling gizi dan pemantauan rutin pertumbuhan.',
                'color' => 'orange'
            ],
            'normal' => [
                'title' => 'Normal',
                'description' => 'Tinggi badan menurut umur berada dalam rentang normal (-2 hingga +2 SD).',
                'recommendation' => 'Lanjutkan praktik pemberian makan saat ini dan pemeriksaan kesehatan rutin.',
                'color' => 'green'
            ],
            'tall' => [
                'title' => 'Tinggi',
                'description' => 'Tinggi badan menurut umur berada di atas +2 SD. Anak lebih tinggi dari rata-rata.',
                'recommendation' => 'Pantau pola pertumbuhan dan pastikan nutrisi seimbang.',
                'color' => 'blue'
            ]
        ];

        return $interpretations[$status];
    }

    private function getBoysData(): array
    {
        if ($this->boysData === null) {
            $this->boysData = $this->loadDataFromCsv('stunting_who_z_score_boys.csv');
        }
        return $this->boysData;
    }

    private function getGirlsData(): array
    {
        if ($this->girlsData === null) {
            $this->girlsData = $this->loadDataFromCsv('stunting_who_z_score_girls.csv');
        }
        return $this->girlsData;
    }

    private function loadDataFromCsv(string $filename): array
    {
        $csvPath = public_path($filename);

        if (!file_exists($csvPath)) {
            throw new \Exception("File CSV tidak ditemukan: {$filename}");
        }

        $data = [];
        $file = fopen($csvPath, 'r');

        if ($file === false) {
            throw new \Exception("Tidak dapat membaca file CSV: {$filename}");
        }

        // Skip header row
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $months = (int) $row[0];

            $data[$months] = [
                '-3sd' => (float) $row[1],
                '-2sd' => (float) $row[2],
                '-1sd' => (float) $row[3],
                'median' => (float) $row[4],
                '1sd' => (float) $row[5],
                '2sd' => (float) $row[6],
                '3sd' => (float) $row[7],
            ];
        }

        fclose($file);

        return $data;
    }
}
