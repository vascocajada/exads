<?php

/*
-- Create `tv_series` table
CREATE TABLE `tv_series` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `channel` VARCHAR(100) NOT NULL,
  `gender` VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Create `tv_series_intervals` table
-- NOTE: This would be a lot easier if show_time was a DATETIME field. But since we have weekday, I'm assuming show_time hast to be a time field.
CREATE TABLE `tv_series_intervals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tv_series` INT NOT NULL,
  `week_day` TINYINT NOT NULL,
  `show_time` TIME NOT NULL,
  FOREIGN KEY (`id_tv_series`) REFERENCES `tv_series`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert TV Series
INSERT INTO `tv_series` (`title`, `channel`, `gender`) VALUES
('Ozark', 'Channel 1', 'non-binary'),
('Game of Thrones', 'Channel 2', 'male'),
('Breaking bad', 'Channel 3', 'female'),
('Sopranos', 'Channel 3', 'female');

-- Insert TV Series Intervals
INSERT INTO `tv_series_intervals` (`id_tv_series`, `week_day`, `show_time`) VALUES
(1, '1', '20:00'),
(1, '6', '09:30'),
(1, '3', '20:00'),
(2, '2', '21:00'),
(2, '3', '05:00'),
(2, '4', '21:00'),
(3, '5', '22:00'),
(3, '1', '23:45'),
(3, '7', '22:00'),
(4, '3', '08:00'),
(4, '3', '12:30'),
(4, '4', '12:30');

 */

class TVGuide {
    const weekDayMap = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];

    private $pdo;

    public function __construct($dsn, $username, $password) {
        try {
            $this->pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
    }

    public function getNextAirTime($dateTime = 'now', $title = null) {
        $dateTime = new DateTime($dateTime);
        $weekDay = $dateTime->format('N');
        $time = $dateTime->format('H:i:s');

        echo "Title: $title\n";
        echo "Weekday: " . self::weekDayMap[$weekDay] . "\n";
        echo "Time: $time\n";

        $nextAirTime = $this->getNextAirTimeThisWeek($weekDay, $time, $title);

        // If there are no airings until the end of the week, check next week
        if (!$nextAirTime) {
            $nextAirTime = $this->getNextAirTimeNextWeek($weekDay, $time, $title);
        }

        if ($nextAirTime) {
            return "Next airing: " . $nextAirTime['title'] . " on " . self::weekDayMap[$nextAirTime['week_day']] . " at " . $nextAirTime['show_time'] . "\n";
        } else {
            return "No airings found based on the given parameters.\n";
        }
    }

    private function getNextAirTimeThisWeek($weekDay, $time, $title = null)
    {
        $query = "SELECT ts.title, tsi.week_day, tsi.show_time
                  FROM tv_series ts
                  JOIN tv_series_intervals tsi ON ts.id = tsi.id_tv_series
                  WHERE (
                    (tsi.week_day = :weekDay AND tsi.show_time > :time)
                    OR tsi.week_day > :weekDay
                  )".($title ? "AND ts.title = :title" : "")."
                  ORDER BY tsi.week_day ASC, tsi.show_time ASC
                  LIMIT 1";

        $stmt = $this->pdo->prepare($query);

        $this->bindParams($weekDay, $time, $title, $stmt);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getNextAirTimeNextWeek($weekDay, $time, $title = null)
    {
        $query = "SELECT ts.title, tsi.week_day, tsi.show_time
                FROM tv_series ts
                JOIN tv_series_intervals tsi ON ts.id = tsi.id_tv_series
                WHERE (
                    (tsi.week_day = :weekDay AND tsi.show_time <= :time)
                    OR tsi.week_day < :weekDay
                )".($title ? "AND ts.title = :title" : "")."
                ORDER BY tsi.week_day ASC, tsi.show_time ASC
                LIMIT 1";

        $stmt = $this->pdo->prepare($query);

        $this->bindParams($weekDay, $time, $title, $stmt);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function bindParams($weekDay, $time, $title, $stmt)
    {
        $stmt->bindParam(':weekDay', $weekDay);
        $stmt->bindParam(':time', $time);
        if ($title) {
            $stmt->bindParam(':title', $title);
        }
    }
}

// Usage
$dsn = 'mysql:host=mysql;dbname=declaree;charset=utf8';
$username = 'root';
$password = 'root';
$tvGuide = new TVGuide($dsn, $username, $password);

// Example: Get the next airing TV series
echo $tvGuide->getNextAirTime();

// Example: Get the next airing TV series for a specific title
echo $tvGuide->getNextAirTime('01/01/2023', 'Game of Thrones');

echo $tvGuide->getNextAirTime('2024-02-22 13:00:00', 'Sopranos');

echo $tvGuide->getNextAirTime('now', 'No Show');
?>
