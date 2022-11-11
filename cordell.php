<?php

if (!class_exists('digiChuck')) {
	class digiChuck {

        const ALLOWED_TASKS     = [
            'ch_random',
            'ch_date',
            'ch_numbers',
            'ch_names',
            'ch_check'
        ];

        const REMOTE_JSON   = 'https://www.digilabs.cz/hiring/data.json';
        const REMOTE_IMAGE  = 'https://www.digilabs.cz/hiring/chuck.jpg';

        private static $instance = null;

        private function __construct() {
        }
    
        public static function getInstance() : self {
            if (self::$instance === null) {
                self::$instance = new self();
            }
    
            return self::$instance;
        }
    
        private static function getConstants() : array {
          $rfClass = new ReflectionClass(static::class);

          return $rfClass->getConstants();
        }

        private function verifyTask(string $task) : bool {
            $task       = filter_input(INPUT_POST, 'ch_task');
            $contsants  = self::getConstants();

            return !in_array($task, $contsants['ALLOWED_TASKS']) ? false : true; 
        }

        public static function showError(string $msg) : never {
            die(json_encode([
                'success'    => false,
                'content'    => $msg
            ], JSON_FORCE_OBJECT));
        }

        private static function fetchRemoteJson() : array {
            $data   = file_get_contents(self::REMOTE_JSON);
            return json_decode($data, true);
        }

        private function splitJokeInHalf(string $joke) : array {
            $firstHalf  = substr($joke, 0, floor(strlen($joke) / 2));
            $secondHalf = substr($joke, floor(strlen($joke) / 2));

            if (substr($firstHalf, 0, -1) != ' ' && substr($secondHalf, 0, 1) != ' ') {
                $middle = strlen($firstHalf) + strpos($secondHalf, ' ') + 1;
            } else {
                $middle = strrpos(substr($joke, 0, floor(strlen($joke) / 2)), ' ') + 1;    
            }

            return [
                'start' => substr($joke, 0, $middle),
                'end'   => substr($joke, $middle)
            ];
        }

        private function getRandomJoke(array $data) : array {
            $short_jokes    = array_filter($data, function($item) {
                return strlen($item['joke']) <= 120;
            });

            $random_item    = array_rand($short_jokes);
            $random_joke    = $short_jokes[$random_item]['joke'];

            return $this->splitJokeInHalf($random_joke);
        }

        private static function getFirstNames(string $fullName) : array {
            $names  = explode(' ', $fullName);
            return array_map(function($n) {
                return mb_substr($n, 0, 1);
            }, $names);
        }

        private function getFunnyNames(array $data) : array {
            $first_letters_only     = array_map(function($item) {
                return [
                    'id'    => $item['id'],
                    'ltrs'  => self::getFirstNames($item['name'])
                ];
            }, $data);

            $funny_names    = array_filter($first_letters_only, function($name) {
                return array_unique($name['ltrs']) === array(reset($name['ltrs']));
            });

            $funny_ids  = array_map(function($item) {
                return $item['id'];
            }, array_values($funny_names));

            return array_filter($data, function($item) use ($funny_ids) {
                return in_array($item['id'], $funny_ids);
            });
        }

        private function getLatestAndUpcoming(array $data) : array {
            return array_filter($data, function($item) {
                $created    = date('Y-m-d', strtotime($item['createdAt']));
                $lastMonth  = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
                $nextMonth  = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));;
                return ($created >= $lastMonth) && ($created <= $nextMonth);
            });
        }

        private function getRightResultsWithEven(array $data) : array {
            $first_is_even  = array_filter($data, function($item) {
                return intval($item['firstNumber']) % 2 == 0;
            });
            return array_filter($first_is_even, function($item) {
                return (intval($item['firstNumber']) / intval($item['secondNumber']) === intval($item['thirdNumber']));
            });
        }

        
        private function substractNumbers(array $numbers) : int {
            $result      = reset($numbers);
            array_shift($numbers);
            
            foreach($numbers as $n) {
                $result     = $result - (int) $n;
            }

            return $result;
        }

        private function getRightResults(array $data) : array {
            $splitted =  array_map(function($item) {
                $split_in_half  = explode(' = ', $item['calculation']);
                return [
                    'id'    => $item['id'],
                    'left'  => reset($split_in_half),
                    'right' => end($split_in_half)
                ];
            }, $data);

            $splitted_to_parts = array_map(function($item) {
                return [
                    'id'    => $item['id'],
                    'left'  => explode(' ', $item['left']),
                    'right' => explode(' ', $item['right']),
                ];
            }, $splitted);

            $calc_ready     = array_map(function($item) {
                return [
                    'id'    => $item['id'],
                    'left'  => [
                        'operation' => in_array('-', $item['left']) ? 'substract' : 'add',
                        'numbers'   => array_filter($item['left'], function($i) {
                            return (int) filter_var($i, FILTER_VALIDATE_INT);
                        })
                    ],
                    'right' => [
                        'operation' => in_array('-', $item['right']) ? 'substract' : 'add',
                        'numbers'   => array_filter($item['right'], function($i) {
                            return (int) filter_var($i, FILTER_VALIDATE_INT);
                        })
                    ]
                ];
            }, $splitted_to_parts);

            $good_results   = array_filter($calc_ready, function($item) {
                $left   = $item['left']['operation']    == 'add' ? array_sum($item['left']['numbers'])  : $this->substractNumbers($item['left']['numbers']);
                $right  = $item['right']['operation']   == 'add' ? array_sum($item['right']['numbers']) : $this->substractNumbers($item['right']['numbers']);

                return $left === $right;
            });

            $good_ids   =   array_map(function($item) {
                return $item['id'];
            }, $good_results);

            return array_filter($data, function($item) use ($good_ids) {
                return in_array($item['id'], $good_ids);
            });
        }

        private function getTaskResult(string $task) : bool|array {
            $instance   = new digiChuck();
            $data       = self::fetchRemoteJson();

            switch ($task) {
                case 'ch_random':
                    return $instance->getRandomJoke($data);
                    break;

                case 'ch_names':
                    return $instance->getFunnyNames($data);
                    break;
                        
                case 'ch_date':
                    return $instance->getLatestAndUpcoming($data);
                    break;
                            
                case 'ch_numbers':
                    return $instance->getRightResultsWithEven($data);
                    break;
    
                case 'ch_check':
                    return $instance->getRightResults($data);
                    break;
                        
                default:
                    return false;
                    break;
            }
        }

        private function prettyPrint(array $task_result, string $task_name) : string {
            ob_start();
            if($task_name === 'ch_random'): ?>
                <div class="ch-joke">
                    <div class="ch-joke__header">
                        <?php echo $task_result['start']; ?>
                    </div>
                    <img src="<?php echo self::REMOTE_IMAGE; ?>" alt="" width="" height="" class="ch-joke__image img-fluid">
                    <div class="ch-joke__footer">
                        <?php echo $task_result['end']; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if(empty($task_result)): ?>
                    <p class="alert alert-danger">Je nám líto, ale nenašli jsme žádné výsledky.</p>
                <?php else: ?>
                    <table class="table table-resposnive">
                        <thead>
                            <th>ID</th>
                            <th>Jméno</th>
                            <th>1. číslo</th>
                            <th>2. číslo</th>
                            <th>3. číslo</th>
                            <th>Výpočet</th>
                            <th>Vtípek</th>
                            <th>Vytvořeno dne</th>
                        </thead>
                        <tbody>
                            <?php foreach($task_result as $task): ?>
                                <?php extract($task); ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $name; ?></td>
                                    <td><?php echo $firstNumber; ?></td>
                                    <td><?php echo $secondNumber; ?></td>
                                    <td><?php echo $thirdNumber; ?></td>
                                    <td><?php echo $calculation; ?></td>
                                    <td><?php echo substr($joke, 0, 12).' ...'; ?></td>
                                    <td><?php echo date('d. m. Y', strtotime($createdAt)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif;
            return ob_get_clean();
        }

        public static function processTask(string $task) : never {
            $instance  = new digiChuck();
            $is_valid  = $instance->verifyTask($task);
            
            if(!$is_valid) {
                self::showError('Tento task není povolený');
            }

            $task_result    = $instance->getTaskResult($task);

            die(json_encode([
                'success'    => true,
                'content'    => $instance->prettyPrint($task_result, $task)
            ], JSON_FORCE_OBJECT));
        }
    }
}


$task       = filter_input(INPUT_POST, 'ch_task');
$processor  = digiChuck::getInstance();

if($task === null) {
    $processor::showError('Tento task neumím zpracovat');
} else {
    $processor::processTask($task);
}