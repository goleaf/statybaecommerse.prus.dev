<?php

declare(strict_types=1);

namespace Opencart\Admin\Controller\Extension\VenipakShipping\Event;

class Venipak extends \Opencart\System\Engine\Controller
{
    public function orderInfo(&$route = '', &$data = '', &$template_code = null)
    {
        $order_id = $data['order_id'];
        $venipak_query = $this->db->query('SELECT tracking, packs FROM ' . DB_PREFIX . "venipak_shipping WHERE order_id = '" . (int) $order_id . "' AND status = 'sent' LIMIT 1");

        if ($venipak_query->num_rows > 0) {
            $venipak_tracking_number = $venipak_query->row['tracking'];
            $packs = $venipak_query->row['packs'] ? count(json_decode($venipak_query->row['packs'], true)) : '';
        } else {
            $packs = null;
            $venipak_tracking_number = null;
        }

        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $data['text_venipak_shipping'] = $this->language->get('heading_title');
        $data['venipak_tracking_number'] = $venipak_tracking_number;
        $data['packs_count'] = $packs;

        $template_buffer = $this->getTemplateBuffer($route, $template_code);
        $additionalHtml = <<<'EOT'
		<div class="col">
		<div class="input-group mb-3">
			<div class="form-control border rounded-start">
			<div class="lead p-0">
				<strong>{{ text_venipak_shipping }}</strong>
				<br />
				{% if not venipak_tracking_number %}
				<input id="venipak_pack_count" class = "form-control" placeholder = "Packs" />
				{% else %}
				{{ venipak_tracking_number }} ({{ packs_count }})
				{% endif %}
			</div>
			</div>
			{% if not venipak_tracking_number %}
				<button
				type="button"
				id="button-venipak-dispatch"
				data-bs-toggle="tooltip"
				data-loading-text="{{ text_loading }}"
				data-bs-toggle="tooltip"
				title="{{ button_venipak_dispatch }}"
				class="btn btn-success">
				<i class="fa-solid fa-truck"></i>
				</button>
			{% else %}
				<button
				type="button"
				disabled="disabled"
				data-bs-toggle="tooltip"
				class="btn btn-success">
				<i class="fa-solid fa-refresh"></i>
				</button>
			{% endif %}
		</div>
		</div>

		EOT;
        $search_text = '<button type="button" id="button-commission-remove" data-bs-toggle="tooltip" title="{{ button_commission_remove }}" class="btn btn-danger"><i class="fa-solid fa-minus-circle"></i></button>
                {% endif %}
              </div>
            </div>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $template_buffer);

        $search_text = "$(document).on('click', '#button-invoice', function() {";
        $additionalHtml = <<<'EOT'
				$(document).delegate('#button-venipak-dispatch', 'click', function() {
				    const pack_count = $('#venipak_pack_count').val();
				    $.ajax({
				        url: 'index.php?route=extension/venipak_shipping/shipping/venipak_shipping.venipakDispatch&user_token={{ user_token }}&order_id={{ order_id }}&pack_count=' + pack_count,
				        dataType: 'json',
				        beforeSend: function() {
				            $('#button-venipak-dispatch').button('loading');
				        },
				        complete: function() {
				            $('#button-venipak-dispatch').button('reset');
				        },
				        success: function(json) {
				            $('.alert-dismissible').remove();

				            if (json['error']) {
				                let errors = '';
				                if (json['error']['error'].length > 1) {
				                    for (let err of json['error']['error']) {
				                        errors += err['text'] + '<br>';
				                    }
				                } else {
				                    errors = json['error']['error']['text'];
				                }
				                $('#content > .container-fluid').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + errors + '</div>');
				            }

				            if (json['venipak_tracking_number']) {
				                $('#venipak_tracking_number').html(json['venipak_tracking_number']);
				                $('#button-venipak-dispatch').replaceWith('<button disabled="disabled" class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>');
				            }
				        },
				        error: function(xhr, ajaxOptions, thrownError) {
				            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				        }
				    });
				});
				EOT;

        $output = str_replace($search_text, $additionalHtml . $search_text, $output);
        $template_code = $output;

        return null;

    }

    public function orderList($route, &$data, &$template_code = null)
    {
        $data['venipak_send_orders'] = $this->url->link('extension/venipak_shipping/shipping/venipak_shipping.venipakSendOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['venipak_get_labels'] = $this->url->link('extension/venipak_shipping/shipping/venipak_shipping.venipakGetLabels', 'user_token=' . $this->session->data['user_token'], true);
        $data['column_venipak_tracking'] = $this->language->get('column_venipak_tracking');
        foreach ($data['orders'] as &$order) {
            $shippingJson = json_decode($order['shipping_method'], true);
            if (strpos($shippingJson['code'], 'venipak_shipping') !== false) {
                $base_url = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
                $order['venipak_logo'] = $base_url . 'extension/venipak_shipping/images/catalog/venipak_shipping/venipak-logo.png';

            }
            $venipak_query = $this->db->query('SELECT tracking, status, error_message FROM ' . DB_PREFIX . "venipak_shipping WHERE order_id = '" . (int) $order['order_id'] . "'");

            if ($venipak_query->num_rows) {
                if ($venipak_query->row['status'] === 'sent') {
                    $order['venipak_tracking_number'] = $venipak_query->row['tracking'];
                } else {
                    $order['error_venipak_status'] = $venipak_query->row['error_message'];
                }
            }
        }
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $template_buffer = $this->getTemplateBuffer($route, $template_code);
        $additionalHtml = <<<'EOT'
		<td class="text-start">
			{{ order.customer }}
			{% if order.venipak_logo %}
				<img src="{{order.venipak_logo}}" alt="Venipak" style="max-height: 20px; margin-left: 5px;">
			{% endif %}
		</td>
	EOT;
        $search_text = '<td class="text-start">{{ order.customer }}</td>';
        $output = str_replace($search_text, $additionalHtml, $template_buffer);
        $additionalHtml = <<<'EOT'
		<td class="text-start d-none d-xl-table-cell" style="border-bottom: 2px solid #eeab00; color: #1f1058;">{{ column_venipak_status }}</td>
	EOT;
        $search_text = '{{ column_date_modified }}</a></td>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $output);

        $additionalHtml = <<<'EOT'
		<td class="text-start d-none d-xl-table-cell">
			{% if not order.venipak_tracking_number %}
				{{ order.error_venipak_status }}
			{% else %}
				{{ order.venipak_tracking_number }}
			{% endif %}
		</td>
	EOT;

        $search_text = '<td class="text-start d-none d-xl-table-cell">{{ order.date_modified }}</td>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $output);

        $template_code = $output;

        return null;
    }

    public function orderLabelButtons(&$route = '', &$data = '', &$template_code = null)
    {
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $template_buffer = $this->getTemplateBuffer($route, $template_code);
        $data['venipak_send_orders'] = $this->url->link('extension/venipak_shipping/shipping/venipak_shipping.venipakSendOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['venipak_get_labels'] = $this->url->link('extension/venipak_shipping/shipping/venipak_shipping.venipakGetLabels', 'user_token=' . $this->session->data['user_token'], true);
        $additionalHtml = <<<'EOT'
		<button type="submit" id="button-venipak-orders-send" form="form-order" formaction="{{ venipak_send_orders }}"
			data-toggle="tooltip" class="btn btn-primary" style="color: #1f1058; background-color: #eeab00; border: none; font-weight: bold;">
			<span><i class="fa fa-paper-plane" style="padding-right: 5px;" aria-hidden="true"></i> {{ text_venipak_get_orders }}</span>
		</button>
		<button type="submit" id="button-venipak-labels-get" form="form-order" formaction="{{ venipak_get_labels }}"
			data-toggle="tooltip" class="btn btn-primary" style="color: #1f1058; background-color: #eeab00; border: none; font-weight: bold;" formtarget="_blank">
			<span><i class="fa fa-sticky-note" style="padding-right: 5px;" aria-hidden="true"></i> {{ text_venipak_get_labels }}</span>
		</button>
	EOT;

        $search_text = '<button type="submit" id="button-shipping" form="form-order" formaction="{{ shipping }}" formtarget="_blank" data-bs-toggle="tooltip" title="{{ button_shipping_print }}" class="btn btn-info"><i class="fa-solid fa-truck"></i></button>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $template_buffer);
        $additionalHtml = <<<'EOT'
    $('#button-venipak-orders-send, #button-venipak-labels-get').on('click', function(e) {
        $('#form-order').attr('action', this.getAttribute('formAction'));
    });
    EOT;
        $search_text = <<<'EOT'
    $('#button-shipping, #button-invoice').prop('disabled', true);
    EOT;
        $output = str_replace($search_text, $search_text . $additionalHtml, $output);

        $template_code = $output;

        return null;
    }

    public function addExcludeLockerCheckboxProductForm(&$route = '', &$data = '', &$template_code = null)
    {
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');
        $data['venipak_is_locker_excluded'] = $this->db->query('SELECT venipak_is_locker_excluded FROM `' . DB_PREFIX . "product` WHERE product_id = '" . (int) $data['product_id'] . "'")->row['venipak_is_locker_excluded'] ?? 0;
        $template_buffer = $this->getTemplateBuffer($route, $template_code);
        $additionalHtml = <<<'EOT'
						<div class="row mb-3">
		<label class="col-sm-2 col-form-label" for = "venipak_is_locker_excluded">{{ text_venipak_product_excluded_from_lockers }}</label>
			<div class="col-sm-10" >
			<div class="input-group">
				<div id="input-venipak_is_locker_excluded" class="form-check form-switch form-switch-lg">
				<input type="checkbox" name="venipak_is_locker_excluded" class = "form-check-input" id="venipak_is_locker_excluded" value="1" {% if venipak_is_locker_excluded %} checked{% endif %}/>
			</div>
			</div>
			</div>
		</div>
		EOT;

        $search_text = <<<'EOT'
		<legend>{{ text_specification }}</legend>
		EOT;
        $output = str_replace($search_text, $search_text . $additionalHtml, $template_buffer);
        $template_code = $output;

        return null;
    }

    public function venipakExcludedFromLockerAddData(&$route = '', &$args = '', &$output = '')
    {
        $venipak_is_locker_excluded = isset($args[0]['venipak_is_locker_excluded']) ? 1 : 0;
        $this->db->query('UPDATE `' . DB_PREFIX . "product` SET venipak_is_locker_excluded = '" . (int) $venipak_is_locker_excluded . "' ORDER BY date_added DESC Limit 1");
    }

    public function venipakExcludedFromLockerEditData(&$route = '', &$args = '', &$output = '')
    {
        $product_id = (int) $args[0];
        $venipak_is_locker_excluded = isset($args[1]['venipak_is_locker_excluded']) ? 1 : 0;

        $this->db->query('UPDATE `' . DB_PREFIX . "product` SET venipak_is_locker_excluded = '" . (int) $venipak_is_locker_excluded . "' WHERE product_id = '" . (int) $product_id . "'");
    }

    public function excludedShippingOptionsInProductList(&$route = '', &$data = '', &$template_code = null)
    {

        $data['user_token'] = $this->session->data['user_token'];
        $this->load->language('extension/venipak_shipping/shipping/venipak_shipping');

        foreach ($data['products'] as &$product) {
            $productData = $this->db->query('SELECT venipak_is_locker_excluded FROM `' . DB_PREFIX . 'product` WHERE product_id = ' . (int) $product['product_id']);
            $product['venipak_is_locker_excluded'] = $productData->row['venipak_is_locker_excluded'];

            $productData = $this->db->query('SELECT venipak_is_delivery_excluded FROM `' . DB_PREFIX . 'product` WHERE product_id = ' . (int) $product['product_id']);
            $product['venipak_is_delivery_excluded'] = $productData->row['venipak_is_delivery_excluded'];
        }
        $template_buffer = $this->getTemplateBuffer($route, $template_code);
        $additionalHtml = <<<'EOT'
		  <th class="text-end">{{ text_venipak_product_excluded_from_lockers }}</th>
		  <th class="text-end">{{ text_venipak_product_excluded_from_delivery }}</th>
		EOT;
        $search_text = '{{ column_quantity }}</a></th>';
        if (strpos($template_buffer, $search_text) !== false) {
            $output = str_replace($search_text, $search_text . $additionalHtml, $template_buffer);
        } else {
            // echo 'not found';die;
            // Optional: handle the case where the search text is not found
            $output = $template_buffer; // Or log a message, etc.
        }
        $additionalHtml = <<<'EOT'
			<td class="text-left">
						<input type = "hidden" class = "checkbox-product-id" value = "{{product.product_id}}">
						<input type="checkbox" name="venipak_is_locker_excluded" class="venipak-is-locker-excluded" value="1" {{ product.venipak_is_locker_excluded ? 'checked' : '' }}/>
					</td>
					<td class="text-left">
						<input type = "hidden" class = "checkbox-product-id" value = "{{product.product_id}}">
						<input type="checkbox" name="venipak_is_delivery_excluded" class="venipak-is-delivery-excluded" value="1" {{ product.venipak_is_delivery_excluded ? 'checked' : '' }}/>
					</td>
		EOT;
        $search_text = '<span class="badge bg-success">{{ product.quantity }}</span>
                {% endif %}</td>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $output);
        $additionalHtml = <<<'EOT'
							<script>$('.venipak-is-locker-excluded').on('change',function(){
							let product_id = $(this).closest('tr').find('.checkbox-product-id').val();
							let pickup_value = '';
							if ($(this).is(':checked')) {
							pickup_value = 1; 
			} else {
				pickup_value = 0;
			}
				$.ajax({
				url: 'index.php?route=extension/venipak_shipping/shipping/venipak_shipping.updatePickupCheckbox&user_token={{ user_token }}',
				type: 'post',
				data : {
					product_id : product_id,
					pickup_value : pickup_value

				},
				success: function(response) {
					if (response.success) {
						alert(response.success); // Success message
					} else if (response.error) {
						alert(response.error); // Error message
					}
				},
				error: function(xhr, status, error) {
					alert('An error occurred: ' + error);
				}
				})
			})

			$('.venipak-is-delivery-excluded').on('change',function(){
							let product_id = $(this).closest('tr').find('.checkbox-product-id').val();
							let pickup_value = '';
							if ($(this).is(':checked')) {
							pickup_value = 1; 
			} else {
				pickup_value = 0;
			}
				$.ajax({
				url: 'index.php?route=extension/venipak_shipping/shipping/venipak_shipping.updateDeliveryCheckbox&user_token={{ user_token }}',
				type: 'post',
				data : {
					product_id : product_id,
					pickup_value : pickup_value

				},
				success: function(response) {
					if (response.success) {
						alert(response.success); // Success message
					} else if (response.error) {
						alert(response.error); // Error message
					}
				},
				error: function(xhr, status, error) {
					alert('An error occurred: ' + error);
				}
				})
			})
				</script>
			EOT;
        $search_text = '</form>';
        $output = str_replace($search_text, $search_text . $additionalHtml, $output);
        $template_code = $output;

        return null;

    }

    protected function getTemplateBuffer($route, $event_template_buffer)
    {
        if ($event_template_buffer) {
            return $event_template_buffer;
        }
        if ($this->isAdmin()) {
            $dir_template = DIR_TEMPLATE;
        } else {
            if ($this->config->get('config_theme') == 'default') {
                $theme = $this->config->get('theme_default_directory');
            } else {
                $theme = $this->config->get('config_theme');
            }
            $dir_template = DIR_TEMPLATE . $theme . '/template/';
        }
        $template_file = $dir_template . $route . '.twig';
        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }
        if ($this->isAdmin()) {
            trigger_error("Cannot find template file for route '$route'");
            exit;
        }
        $dir_template = DIR_TEMPLATE . 'default/template/';
        $template_file = $dir_template . $route . '.twig';
        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }
        trigger_error("Cannot find template file for route '$route'");
        exit;
    }

    protected function isAdmin()
    {
        return defined('DIR_CATALOG') ? true : false;
    }

    protected function modCheck($file)
    {
        $original_file = $file;
        if (defined('DIR_MODIFICATION')) {
            if ($this->startsWith($file, DIR_APPLICATION)) {
                if ($this->isAdmin()) {
                    if (file_exists(DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION));
                    }
                } else {
                    if (file_exists(DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION));
                    }
                }
            } elseif ($this->startsWith($file, DIR_SYSTEM)) {
                if (file_exists(DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM)))) {
                    $file = DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM));
                }
            }
        }
        if (array_key_exists('vqmod', get_defined_vars())) {
            trigger_error("You are using an old VQMod version '2.3.2' or earlier, please upgrade your VQMod!");
            exit;
        }

        if (class_exists('VQMod', false)) {
            if (VQMod::$directorySeparator) {
                if (strpos($file, 'vq2-') !== false) {
                    return $file;
                }
                if (version_compare(VQMod::$_vqversion, '2.5.0', '<')) {
                    trigger_error("You are using an old VQMod version '" . VQMod::$_vqversion . "', please upgrade your VQMod!");
                    exit;
                }
                if ($original_file != $file) {
                    return VQMod::modCheck($file, $original_file);
                }

                return VQMod::modCheck($original_file);
            }
        }

        // no VQmod
        return $file;
    }

    protected function startsWith($haystack, $needle)
    {
        if (strlen($haystack) < strlen($needle)) {
            return false;
        }

        return substr($haystack, 0, strlen($needle)) == $needle;
    }

    public function saveAdminConfiguration(&$route = '', &$data = '', &$template_code = null)
    {
        $query = $this->db->query('SELECT * FROM `' . DB_PREFIX . "setting` WHERE `code` = 'shipping_venipak_shipping' AND `code` != 'shipping_venipak_shipping_last_tracking_number' AND `key` != 'shipping_venipak_shipping_status'");
        if ($query->num_rows) {
            $this->db->query('DELETE FROM `' . DB_PREFIX . 'venipak_shipping_admin_setting`');
            foreach ($query->rows as $setting) {
                $this->db->query('INSERT INTO `' . DB_PREFIX . "venipak_shipping_admin_setting` SET 
					`store_id` = '" . (int) $setting['store_id'] . "',
					`code` = '" . $this->db->escape($setting['code']) . "',
					`key` = '" . $this->db->escape($setting['key']) . "',
					`value` = '" . $this->db->escape($setting['value']) . "',
					`serialized` = '" . $this->db->escape($setting['serialized']) . "'");
            }
        }
    }
}
