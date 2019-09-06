<?php

namespace RobotJoosen;

use modX;

/**
 * Class ParseData
 * @package RobotJoosen\ParseData
 */
class ParseData
{

    /**
     * @var modX
     */
    public $modx;

    /**
     * @var array
     */
    public $config;

    /**
     * @var object|null
     */
    private $pdo;

    /**
     * ParseData constructor.
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $this->pdo = $this->modx->getService('pdoTools');
        $this->config = array_merge([
            'namespace' => 'parsedata',
            'type' => 'tv',
            'name' => null,
            'tpl' => null,
            'tplWrapper' => null,
            'tplEmpty' => null,
            'limit' => 0,
            'offset' => 0,
            'parameters' => [],
            'separator' => "",
            'toPlaceholder' => null
        ], $config);
    }

    /**
     * @return string|array|null
     */
    public function parse()
    {
        if (!$input = $this->getInput()) {
            return (!empty($this->config['emptyTpl'])) ? $this->pdo->getChunk($this->config['emptyTpl']) : null;
        }
        if (!$this->prepareTpl()) {
            return $input;
        }

        $this->config['parameters'] = (!is_array($this->config['parameters'])) ? json_decode($this->config['parameters'], 1) : [];
        $this->config['limit'] = ($this->config['limit'] > 0) ? $this->config['limit'] : count($input);
        $list = [];
        for ($i = $this->config['offset']; $i < $this->config['limit'] + $this->config['offset']; $i++) {
            if ($input[$i]) {
                foreach ($input[$i] as $k => $v) {
                    if (substr($v, 0, 2) === '--') {
                        $input[$i][$k] = str_replace('--', '', $v);
                        if (is_numeric($input[$i][$k])) {
                            $input[$i][$k] = $this->modx->makeUrl($input[$i][$k]);
                        }
                    }
                    if (is_array($v)) {
                        for ($c = 0; $c < count($input[$i][$k]); $c++) {
                            $input[$i][$k . '_' . $c] = $input[$i][$k][$c];
                        }
                    }
                    $input[$i][$k] = nl2br($v);
                }
                $input[$i] = array_merge($input[$i], $this->config['parameters']);
                $input[$i]['idx'] = $i;
                $tpl = $this->config['tpl'][$i % count($this->config['tpl'])];
                $list[] = $this->pdo->getChunk($tpl, $input[$i]);
            }
        }

        if ($this->config['tplWrapper']) {
            $output = $this->pdo->getChunk($this->config['tplWrapper'], ['list' => implode($this->config['separator'], $list)]);
        } else {
            $output = implode($this->config['separator'], $list);
        }

        if (!is_null($this->config['toPlaceholder'])) {
            $this->modx->setPlaceholder($this->config['toPlaceholder'], $output);
        } else {
            return $output;
        }
    }

    /**
     * Find Field in Indexed 2 Dimensional Array
     * @param $key
     * @param $value
     * @return string
     */
    public function findField($key, $value) : string
    {
        $properties = [];
        if ($input = $this->getInput()) {
            foreach ($input as $row) {
                if ($row[$key] === $value) {
                    $properties = $row;
                }
            }
            return ($this->config['tpl']) ? $this->pdo->getChunk($this->config['tpl'], $properties) : json_encode($properties);
        }
        return '';
    }

    /**
     * @return string|null
     */
    private function prepareTpl()
    {
        if ($this->config['tpl']) {
            $this->config['tpl'] = explode(',', $this->config['tpl']);
            return true;
        }
        return null;
    }

    /**
     * Get input from source
     * @return array
     */
    private function getInput()
    {

        switch ($this->config['type']) {
            case 'json' :
                return $this->modx->fromJSON($this->config['name']);
                break;
            case 'placeholder' :
                return json_decode($this->modx->getPlaceholder($this->config['name']), true);
                break;
            case 'tv' :
                if (ctype_digit($this->config['resourceId'])) {
                    if ($resource = $this->modx->getObject('modResource', $this->config['resourceId'])) {
                        return json_decode($resource->getTVValue($this->config['name']), true);
                    }
                } else {
                    return json_decode($this->modx->resource->getTVValue($this->config['name']), true);
                }
                break;
        }
        return [];
    }

}