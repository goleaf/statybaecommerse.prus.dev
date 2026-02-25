<?php

declare(strict_types=1);

namespace Opencart\Catalog\Controller\Extension\VenipakShipping\Shipping;

class VenipakShipping extends \Opencart\System\Engine\Controller
{
    public function pickups()
    {
        $this->load->model('localisation/country');
        $country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_address']['country_id']);
        $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_venipak_shipping_weight_class'));
        $is_locker_disabled = $this->config->get('shipping_venipak_shipping_disable_locker_' . $this->session->data['shipping_address']['zone_id']);
        $is_pickup_disabled = ($weight > 10) ? 'on' : $this->config->get('shipping_venipak_shipping_disable_pickup_' . $this->session->data['shipping_address']['zone_id']);
        $pick_up_type = 0;

        if (empty($is_locker_disabled) && $is_pickup_disabled) {
            $pick_up_type = 3;
        }

        if (empty($is_pickup_disabled) && $is_locker_disabled) {
            $pick_up_type = 1;
        }

        $pickups_collection = file_get_contents('https://go.venipak.lt/ws/get_pickup_points?country=' . $country_info['iso_code_2'] . '&pick_up_type=' . $pick_up_type);
        $pickups_collection_array = json_decode($pickups_collection, true);
        foreach ($pickups_collection_array as &$point) {
            $point['name'] = str_replace(['Venipak locker, ', 'Venipak Pickup, '], '', $point['name']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($pickups_collection_array));
    }

    public function settings()
    {
        $settings = [
            'is_map_enabled'                => $this->config->get('shipping_venipak_shipping_is_map_enabled'),
            'googlemap_api_key'             => $this->config->get('shipping_venipak_shipping_google_api_key'),
            'is_clusters_enabled'           => $this->config->get('shipping_venipak_shipping_is_clusters_enabled'),
            'is_pickup_type_locker_enabled' => empty($is_locker_disabled),
            'is_pickup_type_pickup_enabled' => empty($is_pickup_disabled),
            'locker_marker'                 => $this->config->get('config_url') . 'image/catalog/venipak_shipping/venipak-marker.svg',
            'pickup_marker'                 => $this->config->get('config_url') . 'image/catalog/venipak_shipping/venipak-marker.svg',
        ];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($settings));
    }

    public function pickupSelectBoxLanguageTranslation()
    {
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');

        $json = [
            'text_pickup_select_option' => $this->language->get('text_pickup_select_option'),
        ];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
