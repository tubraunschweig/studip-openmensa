<?php

class OMModel
{
    public function getPublicCanteens()
    {
        $cache = StudipCacheFactory::getCache();
        $return = unserialize($cache->read('OpenMensa/Canteens'));

        if (empty($return)) {
            $return = $this->requestOpenMensaAPI('https://openmensa.org/api/v2/canteens');
            $cache->write('OpenMensa/Canteens', serialize($return));
        }
        return $return;
    }

    public function updatePublicCanteens()
    {
        $cache = StudipCacheFactory::getCache();
        $return = $this->requestOpenMensaAPI('https://openmensa.org/api/v2/canteens');
        $cache->write('OpenMensa/Canteens', serialize($return));
    }

    public function expireCache()
    {
        $cache = StudipCacheFactory::getCache();
        $canteens=$this->getCanteens(false);
        foreach ($canteens as $canteen) {
            $cache->expire('OpenMensa/Canteens/'.$canteen);
        }
        $cache->expire('OpenMensa/Canteens');
    }

    public function getCanteens($meals=true)
    {
        $cache = StudipCacheFactory::getCache();
        $canteens=unserialize(Config::get()->getValue('OM_canteens'))['canteens'];
        if (!empty($canteens) && $meals) {
            $canteens_cache=array();
            foreach ($canteens as $canteen) {
                $canteen_cache=unserialize($cache->read('OpenMensa/Canteens/'.$canteen));
                if (empty($canteen_cache)) {
                    $canteen_cache['info']= $this->requestOpenMensaAPI('https://openmensa.org/api/v2/canteens/'.$canteen);
                    $canteen_cache['days']= $this->requestOpenMensaAPI('https://openmensa.org/api/v2/canteens/'.$canteen.'/days');
                    $meals=array();
                    if (!empty($canteen_cache['days'])) {
                        foreach ($canteen_cache['days'] as $day) {
                            $meals[$day->date]=$this->requestOpenMensaAPI('https://openmensa.org/api/v2/canteens/'.$canteen.'/days/'.$day->date.'/meals');
                        }
                    }
                    $canteen_cache['meals']=$meals;
                    $cache->write('OpenMensa/Canteens/'.$canteen, serialize($canteen_cache));
                }
                $canteens_cache[$canteen]=$canteen_cache;
            }
            return $canteens_cache;
        } else {
            return $canteens;
        }
    }

    public function getDefaultCanteen()
    {
        $config=unserialize(Config::get()->getValue('OM_canteens'));
        return (is_array($config) && !empty($config) && array_key_exists('default_canteen', $config) ? $config['default_canteen'] : false);
    }

    public function getOverview()
    {
        $config=unserialize(Config::get()->getValue('OM_canteens'));
        return (is_array($config) && !empty($config) && array_key_exists('overview', $config) ? $config['overview'] : false);
    }

    public function setCanteens($canteens)
    {
        Config::get()->store('OM_canteens', serialize($canteens));
    }

    private function requestOpenMensaAPI($url)
    {
        $ch = curl_init();
        $headers = [];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        }
        );

        $data=json_decode(curl_exec($ch));

        if (isset($headers['link']) && !empty($headers['link'])) {
            while (isset($this->parse($headers['link'][0])['next'])) {
                curl_setopt($ch, CURLOPT_URL, $this->parse($headers['link'][0])['next'][0]['uri']);
                $headers = [];
                $data=array_merge($data, json_decode(curl_exec($ch)));
            }
        }

        return $data;
    }

    private function parse($link_values)
    {
        if (is_string($link_values)) {
            $link_values = array($link_values);
        }

        $links = array();

        foreach ($link_values as $link_value) {
            $state = 'link_start';
            $link = array();
            $uri = $param_name = $param_value = '';

            $link_value = trim($link_value);

            $len = strlen($link_value);

            foreach (str_split($link_value) as $chr) {
                switch ($state) {
            case 'link_start':
              if ('<' == $chr) {
                  $state = 'uri_start';
                  $uri = '';
                  $link = array();
              }
              break;
            case 'uri_start':
              if ('>' == $chr) {
                  $state = 'uri_end';
                  $link['uri'] = $uri;
              } else {
                  $uri .= $chr;
              }
              break;
            case 'uri_end':
              if (';' == $chr) {
                  $state = 'param_start';
              }
              break;
            case 'param_start':
              if (!$this->_is_whitespace($chr)) {
                  $state = 'param_name_start';
                  $param_name = $chr;
              }
              break;
            case 'param_name_start':
              if ('=' == $chr) {
                  $state = 'param_name_end';
              } else {
                  $param_name .= $chr;
              }
              break;
            case 'param_name_end':
              $param_value = '';
              if ('"' == $chr) {
                  $state = 'quoted_param_value_start';
              } else {
                  $state = 'param_value_start';
              }
              break;
            case 'quoted_param_value_start':
              if ('"' == $chr) {
                  $state = 'quoted_param_value_end';
              } else {
                  $param_value .= $chr;
              }
              break;
            case 'quoted_param_value_end':
              if (';' == $chr) {
                  $state = 'param_value_end';
              } elseif (',' == $chr) {
                  $state = 'end_of_params';
              }
              break;
            case 'param_value_start':
              if (';' == $chr) {
                  $state = 'param_value_end';
              } elseif (',' == $chr) {
                  $state = 'end_of_params';
              } else {
                  $param_value .= $chr;
              }
              break;
            case 'param_value_end':
              $state = 'param_start';
              $link[$param_name] = $param_value;
              break;
            case 'end_of_params':
              $state = 'link_start';
              $link[$param_name] = $param_value;
              if (isset($link['rel'])) {
                  $rels = $link['rel'];
                  unset($link['rel']);
                  foreach (explode(' ', $rels) as $rel) {
                      $links[$rel][] = $link;
                  }
              } else {
                  $links[] = $link;
              }
          }
            }

            if ('link_start' != $state) {
                $link[$param_name] = $param_value;
                if (isset($link['rel'])) {
                    $rels = $link['rel'];
                    unset($link['rel']);
                    foreach (explode(' ', $rels) as $rel) {
                        $links[$rel][] = $link;
                    }
                } else {
                    $links[] = $link;
                }
            }
        }

        return $links;
    }

    private function _is_whitespace($chr)
    {
        return in_array($chr, array(" ", "\t", "\n", "\r", "\0", "\x0B"));
    }
}
