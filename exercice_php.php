<?php 

class DateValueDistributor {
    private $total;
    private $baseline;
    private $start_date;
    private $end_date;

    public function __construct($start_date, $end_date, $total, $baseline) {
        $this->start_date = new DateTime($start_date);
        $this->end_date = new DateTime($end_date);
        $this->total = $total;
        $this->baseline = $baseline;
    }

    public function distributeValues() {
        $result = [];

        $total_weekdays = $this->countWeekdays();

        $value_per_day = ($this->total * (1 - $this->baseline / 100)) / $total_weekdays;

        $current_date = clone $this->start_date;

        while ($current_date <= $this->end_date) {
            $formatted_date = $current_date->format('Y-m-d');
            $weekday = $current_date->format('N'); // 1 (Monday) to 7 (Sunday)

            if ($weekday >= 1 && $weekday <= 5) {
                // Weekday, distribute the value
                $random_factor = mt_rand(80, 120) / 100; // Random factor between 0.8 and 1.2
                $result[$formatted_date] = round($value_per_day * $random_factor, 2);
            } else {
                // Weekend, assign 0
                $result[$formatted_date] = 0;
            }

            $current_date->modify('+1 day');
        }

        // Adjust the total sum to be within 1% of the original total
        $distributed = array_sum($result);
        $adjustment_factor = $this->total / $distributed;
        foreach ($result as $date => $value) {
            $result[$date] = round($value * $adjustment_factor, 2);
        }

        return $result;
    }

    private function countWeekdays() {
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($this->start_date, $interval, $this->end_date);

        $weekdays = 0;
        foreach ($period as $date) {
            $weekday = $date->format('N'); // 1 (Monday) to 7 (Sunday)
            if ($weekday >= 1 && $weekday <= 5) {
                $weekdays++;
            }
        }

        return $weekdays;
    }
}

// Example usage:
$start_date = '2016-12-19';
$end_date = '2016-12-23';
$total = 100;
$baseline = 20;

$distributor = new DateValueDistributor($start_date, $end_date, $total, $baseline);
$result = $distributor->distributeValues();

print_r($result);


?>