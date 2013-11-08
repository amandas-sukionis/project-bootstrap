<?php

namespace Application\Logger;

class Logger implements LoggerInterface
{
    private $writer;
    private $mysqli;
    private $fileName;

    public function addWriter($writer, array $options = null)
    {
        if ($writer == 'database') {
            if ($options) {
                if (isset($options['host']) && isset($options['port']) && isset($options['user'])
                    && isset($options['password'])
                    && isset($options['dbname'])
                ) {
                    $mysqli = mysqli_connect(
                        $options['host'], $options['user'], $options['password'], $options['dbname']
                    );

                    if (mysqli_connect_errno()) {
                        echo "Failed to connect to MySQL: " . mysqli_connect_error();
                    } else {
                        $val = $mysqli->query("select 1 from logs");
                        if ($val !== false) {
                            $this->writer = $writer;
                            $this->mysqli = $mysqli;
                            return true;
                        } else {
                            echo "Table logs was not found, please create it";

                            return false;
                        }
                    }
                } else {
                    echo "Not all options are set";

                    return false;
                }
            } else {
                echo "No options found";

                return false;
            }
        } else if ($writer == 'file') {
            if (!is_dir('logs')) {
                mkdir('logs');
            }

            $fileName = 'logs/' . $options['name'];
            if (!file_exists($fileName)) {
                $ourFileHandle = fopen($fileName, 'w') or die("can't open file");
                fclose($ourFileHandle);
            }

            $this->fileName = $fileName;
            $this->writer = $writer;
            return true;
        } else {
            echo "Writer not found";
            return false;
        }
    }

    public function log($message, $tag = null)
    {
        if (!$tag) {
            $tag = 'DEFAULT';
        }
        if ($this->writer == 'database') {
            if ($stmt = $this->mysqli->prepare("INSERT INTO logs (tag, message, date) VALUES (?, ?, ?)")) {
                $dateNow = date("Y-m-d H:i:s");
                $stmt->bind_param("sss", $tag, $message, $dateNow);

                $stmt->execute();

                $stmt->close();
            }
        } else if ($this->writer == 'file') {
            $dateNow = date("Y-m-d H:i:s");
            $current = file_get_contents($this->fileName);
            $current .= $dateNow . " " . $tag . " " . $message . "\n";
            file_put_contents($this->fileName, $current);
        }
    }
}