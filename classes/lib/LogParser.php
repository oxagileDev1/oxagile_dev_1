<?php

class LogParser {
    const TYPE_USER     = 'user';
    const TYPE_USER_LOG = 'user_log';

    protected $ipPatterns  = '';
    protected $urlPattern  = '(http|ftp|https):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\S*)';
    protected $textPattern = '([\/a-zA-Z0-9;.,_ ]+)';

    public function __construct()
    {
        // ip patterns list
        $this->ipPatterns = implode('|', array(
            'ipv4'         => '(((25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]))',
            'ipv6full'     => '([0-9A-Fa-f]{1,4}(:[0-9A-Fa-f]{1,4}){7})',
            'ipv6null'     => '(::)',
            'ipv6leading'  => '(:(:[0-9A-Fa-f]{1,4}){1,7})',
            'ipv6mid'      => '(([0-9A-Fa-f]{1,4}:){1,6}(:[0-9A-Fa-f]{1,4}){1,6})',
            'ipv6trailing' => '(([0-9A-Fa-f]{1,4}:){1,7}:)',
        ));
    }

    /**
     * get log pattern by type (user|user_log)
     *
     * @param string $type
     *
     * @return string
     * @throws Exception
     */
    protected function getEntryPattern($type)
    {
        switch ($type) {
            case self::TYPE_USER:
                $pattern = '/(?P<ip>' . $this->ipPatterns . ')'
                    . '\|(?P<browser>' . $this->textPattern . ')'
                    . '\|(?P<system>' . $this->textPattern . ')/';
                break;
            case self::TYPE_USER_LOG:
                $pattern = '/(?P<date>\d{4}-\d{2}-\d{2})'
                    . '\|(?P<time>\d{2}:\d{2})'
                    . '\|(?P<ip>'.$this->ipPatterns.')'
                    . '\|(?P<url_from>'.$this->urlPattern.')'
                    . '\|(?P<url_to>'.$this->urlPattern.')/';
                break;
            default:
                throw new \Exception('Wrong parser type');
        }

        return $pattern;
    }

    /**
     * parse log lines into associative array
     *
     * @param string   $type
     * @param resource $data
     * @param array    $keys
     *
     * @return array
     */
    public function parse($type, $data, $keys)
    {
        $i = 1;

        $entries = [];
        $pattern = $this->getEntryPattern($type);

        while (!feof($data)) {
            $row = trim(fgets($data));

            if (preg_match($pattern, $row, $matches)) {
                $entry = [];

                // put each part of the match in an appropriately-named variable
                foreach ($keys as $key) {
                    $entry[$key] = $matches[$key];
                }

                $entries[] = $entry;
            } else {
                // if the line didn't match the pattern
                echo "Can't parse log line $i: $row";
                echo '<br>';
            }

            $i++;
        }

        return $entries;
    }
}
