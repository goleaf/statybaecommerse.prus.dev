<?php

declare(strict_types=1);

namespace Opencart\Catalog\Controller\Extension\VenipakShipping\Event;

class Venipak extends \Opencart\System\Engine\Controller
{
    public function venipakLoadScripts(&$route = '', &$data = '', &$template_code = null)
    {
        if (
            isset($this->request->get['route']) &&
            strpos($this->request->get['route'], 'checkout') !== false
        ) {
            $this->document->addStyle('https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css');
            $this->document->addStyle('extension/venipak_shipping/catalog/view/theme/stylesheet/venipak-shipping/venipak-shipping.css');
            $this->document->addScript('https://unpkg.com/@googlemaps/markerclustererplus/dist/index.min.js');
            $this->document->addScript('extension/venipak_shipping/catalog/view/javascript/venipak_shipping.js');
            $this->document->addScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js');
        }
    }

    public function venipakAdditionalCostToCod(&$route = '', &$data = '', &$output = '')
    {
        $this->load->model('extension/venipak_shipping/total/venipak_shipping_cod');
        ($this->model_extension_venipak_shipping_total_venipak_shipping_cod->getTotal)($data[0], $data[1], $data[2]);
    }
}
