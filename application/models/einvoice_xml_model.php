<?php
class Einvoice_xml_model extends MY_Model{

	public function __construct()
	{
        parent::__construct();
        $this->load->helper('custom_helper');
	}

	public function generate_inv_rec_with_xml( $xmldata ) 
	{
		$xml=simplexml_load_string($xmldata) or die("Error: Cannot create object");
		print_r($xml);
	}

	public function generate_xml_by_id( $data, $consolidated=false )
	{	

		//mandotory validation
		$err = $this->do_validation( $data );

		if (!empty($err)) {
			return array('status' => 'err', 'msg' => implode("\n\r", $err));
		}

		$xml = new DOMDocument('1.0', 'utf-8');
		// Preserve redundant spaces (`true` by default)
		$xml->preserveWhiteSpace = false;

		// Disable automatic document indentation
		$xml->formatOutput = false;

		$invoice = $xml->createElement('Invoice');
		$invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
		$invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
		$xml->appendChild($invoice);

		$invoice_num = $xml->createElement('cbc:ID', $data['invoice_num']);
		$invoice->appendChild($invoice_num);

		//this part should be given by myinvois
		//$billing_ref = $xml->createElement('cac:BillingReference');
		/*$inv_doc_ref = $xml->createElement('cac:InvoiceDocumentReference');
		$uuid = $xml->createElement('cbc:UUID', $this->config->item('einvoice_tin'));
		$id = $xml->createElement('cbc:ID', $doc_num);
		$billing_ref->appendChild($inv_doc_ref);
		$inv_doc_ref->appendChild($uuid);
		$inv_doc_ref->appendChild($id);*/
		//$invoice->appendChild($billing_ref);

		/*$dateTime = new DateTime($data['created_date']);
		$created_date = $dateTime->format('Y-m-d');
		$created_time = $dateTime->format('H:i:s').$dateTime->format('T');*/

		$issue_date = $xml->createElement('cbc:IssueDate', gmdate("Y-m-d"));
		$issue_time = $xml->createElement('cbc:IssueTime', gmdate("H:i:s")."Z");
		$invoice->appendChild($issue_date);
		$invoice->appendChild($issue_time);

		$invoice_type_code = $xml->createElement('cbc:InvoiceTypeCode', $data['xml_type']);
		$invoice_type_code->setAttribute('listVersionID', $data['version']);
		$invoice->appendChild($invoice_type_code);

		$currency_code = $xml->createElement('cbc:DocumentCurrencyCode', $data['cur']);
		$invoice->appendChild($currency_code);

		/*$tax_currency_code = $xml->createElement('cbc:TaxCurrencyCode', $data['cur']);
		$invoice->appendChild($tax_currency_code);

		$tax_exchange_rate = $xml->createElement('cac:TaxExchangeRate');
		$calculation_rate = $xml->createElement('cbc:CalculationRate', $data['cur_rate']);
		$tax_exchange_rate->appendChild($calculation_rate);
		$invoice->appendChild($tax_exchange_rate);

		$note = $xml->createElement('cbc:Note', $data['notes']??'');
		$invoice->appendChild($note);*/

		$inv_period = $xml->createElement('cac:InvoicePeriod');
		$inv_start_date = $xml->createElement('cbc:StartDate', $data['period_start']);
		$inv_end_date = $xml->createElement('cbc:EndDate', $data['period_end']);
		$inv_period->appendChild($inv_start_date);
		$inv_period->appendChild($inv_end_date);
		$invoice->appendChild($inv_period);

		//others???

		//how the customer will pay to us... for this , need to seperate out another setting section for einvoice

		/*if (isset($data['payment_ref'])) {
			if (!empty($data['payment_ref'])) {
				$billing_ref = $xml->createElement('cac:BillingReference');
				$additional_doc_ref = $xml->createElement('cac:AdditionalDocumentReference');
				$ref_id = $xml->createElement('cbc:ID', $data['payment_ref']??'');

				$additional_doc_ref->appendChild($ref_id);
				$billing_ref->appendChild($additional_doc_ref);
				$invoice->appendChild($billing_ref);
			}
		}*/

		//if credit or debit note, need to attached reference
		if ((isset($data['main_doc'])) && ( ($data['xml_type'] == '02') || ($data['xml_type'] == '03') ) ) {
			if (!empty($data['main_doc'])) {
				$billing_ref = $xml->createElement('cac:BillingReference');

				$inv_doc_ref = $xml->createElement('cac:InvoiceDocumentReference');
				$ref_id = $xml->createElement('cbc:ID', $data['main_doc']['id']);
				$uuid = $xml->createElement('cbc:UUID', $data['main_doc']['uuid']);

				$inv_doc_ref->appendChild($ref_id);
				$inv_doc_ref->appendChild($uuid);
				$billing_ref->appendChild($inv_doc_ref);
				$invoice->appendChild($billing_ref);
			}
		}

		//additional doc ref
		if (isset($data['additional_doc'])) {
			if (is_array($data['additional_doc'])) {
				if (!empty($data['additional_doc'])) {
					$billing_ref = $xml->createElement('cac:BillingReference');
					foreach ($data['additional_doc'] as $doc_row) {
						$additional_doc_ref = $xml->createElement('cac:AdditionalDocumentReference');
						$ref_id = $xml->createElement('cbc:ID', $doc_row['id']);
						if (isset($doc_row['doctype'])) {
							$doc_type = $xml->createElement('cbc:DocumentType', $doc_row['doctype']??'');
						}
						if (isset($doc_row['desc'])) {
							$doc_desc = $xml->createElement('cbc:DocumentDescription', $doc_row['desc']??'');
						}

						$additional_doc_ref->appendChild($ref_id);
						if (isset($doc_row['doctype'])) {
							$additional_doc_ref->appendChild($doc_type);
						}
						if (isset($doc_row['desc'])) {
							$additional_doc_ref->appendChild($doc_desc);
						}

						if (isset($doc_row['attachment'])) {
							if (is_array($doc_row['attachment'])) {
								foreach ($doc_row['attachment'] as $attachment) {
									$attachment = $xml->createElement('cac:Attachment');
									$embedded_doc_bindary_obj = $xml->createElement('cbc:EmbeddedDocumentBinaryObject', $attachment['file']??'');
									$embedded_doc_bindary_obj->setAttribute('mimeCode', $attachment['mimetype']??'');
									$attachment->appendChild($embedded_doc_bindary_obj);
									$additional_doc_ref->appendChild($attachment);
								}
							}
						}

						$billing_ref->appendChild($additional_doc_ref);
					}
					$invoice->appendChild($billing_ref);
				}
			}
		}

		//Accounting Supplier Party (Vendor information - supplier)
		if (!empty($data['supplier'])) {
			$accounting_sup_party = $xml->createElement('cac:AccountingSupplierParty');
			$party = $xml->createElement('cac:Party');
			$postal_address = $xml->createElement('cac:PostalAddress');
			$adderss_line_1 = $xml->createElement('cac:AddressLine');
			$line_1 = $xml->createElement('cbc:Line', $data['supplier']['line_1']??'');
			$adderss_line_2 = $xml->createElement('cac:AddressLine');
			$line_2 = $xml->createElement('cbc:Line', $data['supplier']['line_2']??'');
			$adderss_line_3 = $xml->createElement('cac:AddressLine');
			$line_3 = $xml->createElement('cbc:Line', $data['supplier']['line_3']??'');
			$postal_zone = $xml->createElement('cbc:PostalZone', $data['supplier']['postal_zone']??'');
			$city_name = $xml->createElement('cbc:CityName', $data['supplier']['city_name']??'');
			$country_subentity_code = $xml->createElement('cbc:CountrySubentityCode', $data['supplier']['state_code']??'');
			$country = $xml->createElement('cac:Country');
			$idendication_code = $xml->createElement('cbc:IdentificationCode', $data['supplier']['country_code']??'');
			//??? what is this hardcode specification for country?
			$idendication_code->setAttribute('listID', 'ISO3166-1');
			$idendication_code->setAttribute('listAgencyID', '6');
			$party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
			$registration_name = $xml->createElement('cbc:RegistrationName', $data['supplier']['company_name']??'');
			$additional_acc_id = $xml->createElement('cbc:AdditionalAccountID', $data['supplier']['agency_no']??'');
			if (!empty($data['supplier']['agency'])) {
				$additional_acc_id->setAttribute('schemeAgencyName', $data['supplier']['agency']);
			}
			if (!empty($data['supplier']['tin'])) {
				$party_identification_1 = $xml->createElement('cac:PartyIdentification');
				$party_id_1 = $xml->createElement('cbc:ID', $data['supplier']['tin']);
				$party_id_1->setAttribute('schemeID', 'TIN');
			}
			if (!empty($data['supplier']['company_reg_id']) && !empty($data['supplier']['company_reg_type'])) {
				$party_identification_2 = $xml->createElement('cac:PartyIdentification');
				$party_id_2 = $xml->createElement('cbc:ID', $data['supplier']['company_reg_id']);
				$party_id_2->setAttribute('schemeID', $data['supplier']['company_reg_type']);
			}
			if (!empty($data['supplier']['tax_id'])) {
				$party_identification_3 = $xml->createElement('cac:PartyIdentification');
				$party_id_3 = $xml->createElement('cbc:ID', $data['supplier']['tax_id']);
				$party_id_3->setAttribute('schemeID', 'SST');
			}
			if (!empty($data['supplier']['ttx_id'])) {
				$party_identification_4 = $xml->createElement('cac:PartyIdentification');
				$party_id_4 = $xml->createElement('cbc:ID', $data['supplier']['ttx_id']);
				$party_id_4->setAttribute('schemeID', 'TTX');
			}
			$contact = $xml->createElement('cac:Contact');
			$telephone = $xml->createElement('cbc:Telephone', $data['supplier']['tel']??'');
			$email = $xml->createElement('cbc:ElectronicMail', $data['supplier']['email']??'');
			$industry_classification_code = $xml->createElement('cbc:IndustryClassificationCode', $data['supplier']['msic']??'');
			$industry_classification_code->setAttribute('name', $data['supplier']['msic_desc']??'');

			$country->appendChild($idendication_code);

			$postal_address->appendChild($city_name);
			$postal_address->appendChild($postal_zone);
			$postal_address->appendChild($country_subentity_code);
			$postal_address->appendChild($adderss_line_1);
			$adderss_line_1->appendChild($line_1);
			$postal_address->appendChild($adderss_line_2);
			$adderss_line_2->appendChild($line_2);
			$postal_address->appendChild($adderss_line_3);
			$adderss_line_3->appendChild($line_3);
			$postal_address->appendChild($country);
			
			if (!empty($data['supplier']['tin'])) {
				$party_identification_1->appendChild($party_id_1);
			}
			if (!empty($data['supplier']['company_reg_id']) && !empty($data['supplier']['company_reg_type'])) {
				$party_identification_2->appendChild($party_id_2);
			}
			if (!empty($data['supplier']['tax_id'])) {
				$party_identification_3->appendChild($party_id_3);
			}
			if (!empty($data['supplier']['ttx_id'])) {
				$party_identification_4->appendChild($party_id_4);
			}
			$party_legal_entity->appendChild($registration_name);
			//$party_legal_entity->appendChild($additional_acc_id);
			$contact->appendChild($telephone);
			$contact->appendChild($email);
			$party->appendChild($industry_classification_code);
			if (!empty($data['supplier']['tin'])) {
				$party->appendChild($party_identification_1);
			}
			if (!empty($data['supplier']['company_reg_id']) && !empty($data['supplier']['company_reg_type'])) {
				$party->appendChild($party_identification_2);
			}
			if (!empty($data['supplier']['tax_id'])) {
				$party->appendChild($party_identification_3);
			}
			if (!empty($data['supplier']['ttx_id'])) {
				$party->appendChild($party_identification_4);
			}
			$party->appendChild($postal_address);
			$party->appendChild($party_legal_entity);
			$party->appendChild($contact);
			$accounting_sup_party->appendChild($party);
			$invoice->appendChild($accounting_sup_party);
		}

		//Accounting Customer Party (Our information - buyer)
 		if ($consolidated) {
			//buyer will be empty array
			$accounting_cust_party = $xml->createElement('cac:AccountingCustomerParty');
			$party = $xml->createElement('cac:Party');
			$postal_address = $xml->createElement('cac:PostalAddress');
			$adderss_line_1 = $xml->createElement('cac:AddressLine');
			$line_1 = $xml->createElement('cbc:Line', 'NA');
			$adderss_line_2 = $xml->createElement('cac:AddressLine');
			$line_2 = $xml->createElement('cbc:Line', 'NA');
			$adderss_line_3 = $xml->createElement('cac:AddressLine');
			$line_3 = $xml->createElement('cbc:Line', 'NA');
			$postal_zone = $xml->createElement('cbc:PostalZone', 'NA');
			$city_name = $xml->createElement('cbc:CityName', 'NA');
			$country_subentity_code = $xml->createElement('cbc:CountrySubentityCode', '17');
			$country = $xml->createElement('cac:Country');
			$idendication_code = $xml->createElement('cbc:IdentificationCode', 'MYS');
			$idendication_code->setAttribute('listID', 'ISO3166-1');
			$idendication_code->setAttribute('listAgencyID', '6');
			$party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
			$registration_name = $xml->createElement('cbc:RegistrationName', 'General Public');
			$additional_acc_id = $xml->createElement('cbc:AdditionalAccountID', 'NA');
			$party_identification_1 = $xml->createElement('cac:PartyIdentification');
			$party_id_1 = $xml->createElement('cbc:ID', 'EI00000000010');
			$party_id_1->setAttribute('schemeID', 'TIN');
			$party_identification_2 = $xml->createElement('cac:PartyIdentification');
			$party_id_2 = $xml->createElement('cbc:ID', 'NA');
			$party_id_2->setAttribute('schemeID', 'BRN');
			$party_identification_3 = $xml->createElement('cac:PartyIdentification');
			$party_id_3 = $xml->createElement('cbc:ID', 'NA');
			$party_id_3->setAttribute('schemeID', 'SST');
			$contact = $xml->createElement('cac:Contact');
			$telephone = $xml->createElement('cbc:Telephone', 'NA');
			$email = $xml->createElement('cbc:ElectronicMail', 'NA');

			$country->appendChild($idendication_code);

			$postal_address->appendChild($city_name);
			$postal_address->appendChild($postal_zone);
			$postal_address->appendChild($country_subentity_code);
			$postal_address->appendChild($adderss_line_1);
			$adderss_line_1->appendChild($line_1);
			$postal_address->appendChild($adderss_line_2);
			$adderss_line_2->appendChild($line_2);
			$postal_address->appendChild($adderss_line_3);
			$adderss_line_3->appendChild($line_3);
			$postal_address->appendChild($country);

			//$party->appendChild($idendication_code);
			$party_identification_1->appendChild($party_id_1);
			$party_identification_2->appendChild($party_id_2);
			$party_identification_3->appendChild($party_id_3);
			$party_legal_entity->appendChild($registration_name);
			//$party_legal_entity->appendChild($additional_acc_id);
			$contact->appendChild($telephone);
			$contact->appendChild($email);
			$party->appendChild($party_identification_1);
			$party->appendChild($party_identification_2);
			//$party->appendChild($party_identification_3);
			$party->appendChild($postal_address);
			$party->appendChild($party_legal_entity);
			$party->appendChild($contact);
			$accounting_cust_party->appendChild($party);
			$invoice->appendChild($accounting_cust_party);
		} else if (!empty($data['buyer'])) {
			$accounting_cust_party = $xml->createElement('cac:AccountingCustomerParty');
			$party = $xml->createElement('cac:Party');
			$postal_address = $xml->createElement('cac:PostalAddress');
			$adderss_line_1 = $xml->createElement('cac:AddressLine');
			$line_1 = $xml->createElement('cbc:Line', $data['buyer']['line_1']??'');
			$adderss_line_2 = $xml->createElement('cac:AddressLine');
			$line_2 = $xml->createElement('cbc:Line', $data['buyer']['line_2']??'');
			$adderss_line_3 = $xml->createElement('cac:AddressLine');
			$line_3 = $xml->createElement('cbc:Line', $data['buyer']['line_3']??'');
			$postal_zone = $xml->createElement('cbc:PostalZone', $data['buyer']['postal_zone']??'');
			$city_name = $xml->createElement('cbc:CityName', $data['buyer']['city_name']??'');
			$country_subentity_code = $xml->createElement('cbc:CountrySubentityCode', $data['buyer']['state_code']??'');
			$country = $xml->createElement('cac:Country');
			$idendication_code = $xml->createElement('cbc:IdentificationCode', $data['buyer']['country_code']??'');
			$idendication_code->setAttribute('listID', 'ISO3166-1');
			$idendication_code->setAttribute('listAgencyID', '6');
			$party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
			$registration_name = $xml->createElement('cbc:RegistrationName', $data['buyer']['company_name']??'');
			$additional_acc_id = $xml->createElement('cbc:AdditionalAccountID', $data['buyer']['agency_no']??'');
			if (!empty($data['buyer']['agency'])) {
				$additional_acc_id->setAttribute('schemeAgencyName', $data['buyer']['agency']);
			}
			if (!empty($data['buyer']['tin'])) {
				$party_identification_1 = $xml->createElement('cac:PartyIdentification');
				$party_id_1 = $xml->createElement('cbc:ID', $data['buyer']['tin']);
				$party_id_1->setAttribute('schemeID', 'TIN');
			}
			if (!empty($data['buyer']['company_reg_id']) && !empty($data['buyer']['company_reg_type'])) {
				$party_identification_2 = $xml->createElement('cac:PartyIdentification');
				$party_id_2 = $xml->createElement('cbc:ID', $data['buyer']['company_reg_id']);
				$party_id_2->setAttribute('schemeID', $data['buyer']['company_reg_type']);
			}
			if (!empty($data['buyer']['tax_id'])) {
				$party_identification_3 = $xml->createElement('cac:PartyIdentification');
				$party_id_3 = $xml->createElement('cbc:ID', $data['buyer']['tax_id']);
				$party_id_3->setAttribute('schemeID', 'SST');
			}
			if (!empty($data['buyer']['ttx_id'])) {
				$party_identification_4 = $xml->createElement('cac:PartyIdentification');
				$party_id_4 = $xml->createElement('cbc:ID', $data['buyer']['ttx_id']);
				$party_id_4->setAttribute('schemeID', 'TTX');
			}
			$contact = $xml->createElement('cac:Contact');
			$telephone = $xml->createElement('cbc:Telephone', $data['buyer']['tel']??'');
			$email = $xml->createElement('cbc:ElectronicMail', $data['buyer']['email']??'');
			$industry_classification_code = $xml->createElement('cbc:IndustryClassificationCode', $data['buyer']['msic']??'');
			$industry_classification_code->setAttribute('name', $data['buyer']['msic_desc']??'');

			$country->appendChild($idendication_code);

			$postal_address->appendChild($city_name);
			$postal_address->appendChild($postal_zone);
			$postal_address->appendChild($country_subentity_code);
			$postal_address->appendChild($adderss_line_1);
			$adderss_line_1->appendChild($line_1);
			$postal_address->appendChild($adderss_line_2);
			$adderss_line_2->appendChild($line_2);
			$postal_address->appendChild($adderss_line_3);
			$adderss_line_3->appendChild($line_3);
			$postal_address->appendChild($country);

			//$party->appendChild($idendication_code);
			if (!empty($data['buyer']['tin'])) {
				$party_identification_1->appendChild($party_id_1);
			}
			if (!empty($data['buyer']['company_reg_id']) && !empty($data['buyer']['company_reg_type'])) {
				$party_identification_2->appendChild($party_id_2);
			}
			if (!empty($data['buyer']['tax_id'])) {
				$party_identification_3->appendChild($party_id_3);
			}
			if (!empty($data['buyer']['ttx_id'])) {
				$party_identification_4->appendChild($party_id_4);
			}
			$party_legal_entity->appendChild($registration_name);
			//$party_legal_entity->appendChild($additional_acc_id);
			$contact->appendChild($telephone);
			$contact->appendChild($email);
			if (!empty($data['buyer']['tin'])) {
				$party->appendChild($party_identification_1);
			}
			if (!empty($data['buyer']['company_reg_id']) && !empty($data['buyer']['company_reg_type'])) {
				$party->appendChild($party_identification_2);
			}
			if (!empty($data['buyer']['tax_id'])) {
				$party->appendChild($party_identification_3);
			}
			if (!empty($data['buyer']['ttx_id'])) {
				$party->appendChild($party_identification_4);
			}
			$party->appendChild($postal_address);
			$party->appendChild($party_legal_entity);
			$party->appendChild($contact);
			//$party->appendChild($industry_classification_code);
			$accounting_cust_party->appendChild($party);
			$invoice->appendChild($accounting_cust_party);
		}

		//delivery (Shipping Recipient - delivery)
		if ($consolidated) {
			//delivery will be empty array()
			$delivery = $xml->createElement('cac:Delivery');
			$delivery_party = $xml->createElement('cac:DeliveryParty');
			$party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
			$registration_name = $xml->createElement('cbc:RegistrationName', 'General Public');
			$postal_address = $xml->createElement('cac:PostalAddress');
			$adderss_line_1 = $xml->createElement('cac:AddressLine');
			$line_1 = $xml->createElement('cbc:Line', 'NA');
			$adderss_line_2 = $xml->createElement('cac:AddressLine');
			$line_2 = $xml->createElement('cbc:Line', 'NA');
			$adderss_line_3 = $xml->createElement('cac:AddressLine');
			$line_3 = $xml->createElement('cbc:Line', 'NA');
			$postal_zone = $xml->createElement('cbc:PostalZone', 'NA');
			$city_name = $xml->createElement('cbc:CityName', 'NA');
			$country_subentity_code = $xml->createElement('cbc:CountrySubentityCode', '17');
			$country = $xml->createElement('cac:Country');
			$idendication_code = $xml->createElement('cbc:IdentificationCode', 'MYS');
			$idendication_code->setAttribute('listID', 'ISO3166-1');
			$idendication_code->setAttribute('listAgencyID', '6');
			$party_identification_1 = $xml->createElement('cac:PartyIdentification');
			$party_id_1 = $xml->createElement('cbc:ID', 'EI00000000010');
			$party_id_1->setAttribute('schemeID', 'TIN');
			$party_identification_2 = $xml->createElement('cac:PartyIdentification');
			$party_id_2 = $xml->createElement('cbc:ID', 'NA');
			$party_id_2->setAttribute('schemeID', 'BRN');
			$party_identification_3 = $xml->createElement('cac:PartyIdentification');
			$party_id_3 = $xml->createElement('cbc:ID', 'NA');
			$party_id_3->setAttribute('schemeID', 'SST');

			$country->appendChild($idendication_code);

			$postal_address->appendChild($city_name);
			$postal_address->appendChild($postal_zone);
			$postal_address->appendChild($country_subentity_code);
			$postal_address->appendChild($adderss_line_1);
			$adderss_line_1->appendChild($line_1);
			$postal_address->appendChild($adderss_line_2);
			$adderss_line_2->appendChild($line_2);
			$postal_address->appendChild($adderss_line_3);
			$adderss_line_3->appendChild($line_3);
			$postal_address->appendChild($country);
			//$party->appendChild($idendication_code);
			$party_identification_1->appendChild($party_id_1);
			$party_identification_2->appendChild($party_id_2);
			$party_identification_3->appendChild($party_id_3);

			$party_legal_entity->appendChild($registration_name);
			$delivery_party->appendChild($party_identification_1);
			$delivery_party->appendChild($party_identification_2);
			//$delivery_party->appendChild($party_identification_3);
			$delivery_party->appendChild($postal_address);
			$delivery_party->appendChild($party_legal_entity);
			$delivery->appendChild($delivery_party);

			$invoice->appendChild($delivery);
		} else if (!empty($data['delivery'])) {
			$delivery = $xml->createElement('cac:Delivery');
			$delivery_party = $xml->createElement('cac:DeliveryParty');
			$party_legal_entity = $xml->createElement('cac:PartyLegalEntity');
			$registration_name = $xml->createElement('cbc:RegistrationName', $data['delivery']['company_name']??'');
			$postal_address = $xml->createElement('cac:PostalAddress');
			$adderss_line_1 = $xml->createElement('cac:AddressLine');
			$line_1 = $xml->createElement('cbc:Line', $data['delivery']['line_1']??'');
			$adderss_line_2 = $xml->createElement('cac:AddressLine');
			$line_2 = $xml->createElement('cbc:Line', $data['delivery']['line_2']??'');
			$adderss_line_3 = $xml->createElement('cac:AddressLine');
			$line_3 = $xml->createElement('cbc:Line', $data['delivery']['line_3']??'');
			$postal_zone = $xml->createElement('cbc:PostalZone', $data['delivery']['postal_zone']??'');
			$city_name = $xml->createElement('cbc:CityName', $data['delivery']['city_name']??'');
			$country_subentity_code = $xml->createElement('cbc:CountrySubentityCode', $data['delivery']['state_code']??'');
			$country = $xml->createElement('cac:Country');
			$idendication_code = $xml->createElement('cbc:IdentificationCode', $data['delivery']['country_code']??'');
			$idendication_code->setAttribute('listID', 'ISO3166-1');
			$idendication_code->setAttribute('listAgencyID', '6');
			if (!empty($data['delivery']['tin'])) {
				$party_identification_1 = $xml->createElement('cac:PartyIdentification');
				$party_id_1 = $xml->createElement('cbc:ID', $data['delivery']['tin']);
				$party_id_1->setAttribute('schemeID', 'TIN');
			}
			if (!empty($data['delivery']['company_reg_id']) && !empty($data['delivery']['company_reg_type'])) {
				$party_identification_2 = $xml->createElement('cac:PartyIdentification');
				$party_id_2 = $xml->createElement('cbc:ID', $data['delivery']['company_reg_id']);
				$party_id_2->setAttribute('schemeID', $data['delivery']['company_reg_type']);
			}
			if (!empty($data['delivery']['tax_id'])) {
				$party_identification_3 = $xml->createElement('cac:PartyIdentification');
				$party_id_3 = $xml->createElement('cbc:ID', $data['delivery']['tax_id']);
				$party_id_3->setAttribute('schemeID', 'SST');
			}
			if (!empty($data['delivery']['ttx_id'])) {
				$party_identification_4 = $xml->createElement('cac:PartyIdentification');
				$party_id_4 = $xml->createElement('cbc:ID', $data['delivery']['ttx_id']);
				$party_id_4->setAttribute('schemeID', 'TTX');
			}

			$country->appendChild($idendication_code);

			$postal_address->appendChild($city_name);
			$postal_address->appendChild($postal_zone);
			$postal_address->appendChild($country_subentity_code);
			$postal_address->appendChild($adderss_line_1);
			$adderss_line_1->appendChild($line_1);
			$postal_address->appendChild($adderss_line_2);
			$adderss_line_2->appendChild($line_2);
			$postal_address->appendChild($adderss_line_3);
			$adderss_line_3->appendChild($line_3);
			$postal_address->appendChild($country);
			//$party->appendChild($idendication_code);
			if (!empty($data['delivery']['tin'])) {
				$party_identification_1->appendChild($party_id_1);
			}
			if (!empty($data['delivery']['company_reg_id']) && !empty($data['delivery']['company_reg_type'])) {
				$party_identification_2->appendChild($party_id_2);
			}
			if (!empty($data['delivery']['tax_id'])) {
				$party_identification_3->appendChild($party_id_3);
			}
			if (!empty($data['delivery']['ttx_id'])) {
				$party_identification_4->appendChild($party_id_4);
			}

			$party_legal_entity->appendChild($registration_name);
			if (!empty($data['delivery']['tin'])) {
				$delivery_party->appendChild($party_identification_1);
			}
			if (!empty($data['delivery']['company_reg_id']) && !empty($data['delivery']['company_reg_type'])) {
				$delivery_party->appendChild($party_identification_2);
			}
			if (!empty($data['delivery']['tax_id'])) {
				$delivery_party->appendChild($party_identification_3);
			}
			if (!empty($data['delivery']['ttx_id'])) {
				$delivery_party->appendChild($party_identification_4);
			}
			$delivery_party->appendChild($postal_address);
			$delivery_party->appendChild($party_legal_entity);
			$delivery->appendChild($delivery_party);

			if (isset($data['delivery']['freight_charges'])) {
				if (isset($data['delivery']['freight_charges']['amt'])) {
					$freight_id = $xml->createElement('cbc:ID', $data['delivery']['freight_charges']['ref']??'');
					$shipment = $xml->createElement('cac:Shipment');
					$freight_allowance_charge = $xml->createElement('cac:FreightAllowanceCharge');
					$charge_indicator = $xml->createElement('cbc:ChargeIndicator', 'true');
					$amount = $xml->createElement('cbc:Amount', $data['delivery']['freight_charges']['amt']??0);
					$amount->setAttribute('currencyID', $data['delivery']['freight_charges']['cur']??'');
					$allowance_charge_reason = $xml->createElement('cbc:AllowanceChargeReason', $data['delivery']['freight_charges']['reason']??'');

					$freight_allowance_charge->appendChild($charge_indicator);
					$freight_allowance_charge->appendChild($allowance_charge_reason);
					$freight_allowance_charge->appendChild($amount);
					$shipment->appendChild($freight_id);
					$shipment->appendChild($freight_allowance_charge);
					$delivery->appendChild($shipment);
				}
			}

			$invoice->appendChild($delivery);
		}

		$payment_mean = $xml->createElement('cac:PaymentMeans');
		$payment_mean_code = $xml->createElement('cbc:PaymentMeansCode', $data['payment_method']??'');
		$payee_financial_acc = $xml->createElement('cac:PayeeFinancialAccount');
		$payment_id = $xml->createElement('cbc:ID', $data['payment_acc']??'');

		$payment_mean->appendChild($payment_mean_code);
		$payee_financial_acc->appendChild($payment_id);
		$payment_mean->appendChild($payee_financial_acc);
		$invoice->appendChild($payment_mean);

		$payment_term = $xml->createElement('cac:PaymentTerms');
		//$instruction_note = $xml->createElement('cbc:Note', $data['payment_term']);
		$instruction_note = $xml->createElement('cbc:Note', $data['notes']??'');

		$payment_term->appendChild($instruction_note);
		$invoice->appendChild($payment_term);

		if (isset($data['prepaid'])) {

			if (!empty($data['prepaid']['id'])) {

				$prepaid_payment = $xml->createElement('cac:PrepaidPayment');
				$prepaid_id = $xml->createElement('cbc:ID', $data['prepaid']['id']??'');
				$paid_amount = $xml->createElement('cbc:PaidAmount', $data['prepaid']['amt']??'');
				$paid_amount->setAttribute('currencyID', $data['prepaid']['cur']??'');

				if (!empty($data['prepaid']['date'])) {
					/*$prepaid_paid = new DateTime($data['prepaid']['date']);
					$prepaid_date = $dateTime->format('Y-m-d');
					$prepaid_time = $dateTime->format('H:i:s');*/

					$prepaid_date = gmdate('Y-m-d', strtotime($data['prepaid']['date']));
					$prepaid_time = gmdate('H:i:s', strtotime($data['prepaid']['date'])).'Z';
				} else {
					$prepaid_date = '';
					$prepaid_time = '';
				}

				$paid_date = $xml->createElement('cbc:PaidDate', $prepaid_date);
				$paid_time = $xml->createElement('cbc:PaidTime', $prepaid_time);

				$prepaid_payment->appendChild($prepaid_id);
				$prepaid_payment->appendChild($paid_amount);
				$prepaid_payment->appendChild($paid_date);
				$prepaid_payment->appendChild($paid_time);
				$invoice->appendChild($prepaid_payment);

			}

		}

		//allowance charges
		if (isset($data['allowance'])) { 
			if (is_array($data['allowance'])) {
				foreach ($data['allowance'] as $allowance_row) {
					$allowance_charge = $xml->createElement('cac:AllowanceCharge');
					$charge_indicator = $xml->createElement('cbc:ChargeIndicator', $allowance_row['indicator']??'');
					$charge_reason = $xml->createElement('cbc:AllowanceChargeReason', $allowance_row['reason']??'');
					$amount = $xml->createElement('cbc:Amount', $allowance_row['amt']??0);
					$amount->setAttribute('currencyID', $allowance_row['cur']??'');
					//$multiplier_factor_numeric = $xml->createElement('cbc:MultiplierFactorNumeric', $allowance_row['percent']??'');

					$allowance_charge->appendChild($charge_indicator);
					$allowance_charge->appendChild($charge_reason);
					$allowance_charge->appendChild($amount);
					//$allowance_charge->appendChild($multiplier_factor_numeric);
					$invoice->appendChild($allowance_charge);
				}
			}
		}

		//tax total
		$tax_total = $xml->createElement('cac:TaxTotal');
		$tax_amount = $xml->createElement('cbc:TaxAmount', $data['grand_tax']??0);
		$tax_amount->setAttribute('currencyID', $data['grand_tax_cur']??'');
		$tax_amount2 = $xml->createElement('cbc:TaxAmount', $data['grand_tax']??0);
		$tax_amount2->setAttribute('currencyID', $data['grand_tax_cur']??'');
		$tax_subtotal = $xml->createElement('cac:TaxSubtotal');
		$taxable_amount = $xml->createElement('cbc:TaxableAmount', $data['grand_taxable']??0);
		$taxable_amount->setAttribute('currencyID', $data['grand_taxable_cur']??'');
		$tax_category = $xml->createElement('cac:TaxCategory');
		$tax_exemption_reason = $xml->createElement('cbc:TaxExemptionReason', $data['grand_tax_exemption_reason']??'');
		$tax_scheme = $xml->createElement('cac:TaxScheme');
		$tax_id = $xml->createElement('cbc:ID', $data['grand_tax_code']??'');
		$tax_id_2 = $xml->createElement('cbc:ID', 'OTH');
		$tax_id_2->setAttribute('schemeID', "UN/ECE 5153");
		$tax_id_2->setAttribute('schemeAgencyID', "6");
		$tax_type_code = $xml->createElement('cbc:TaxTypeCode', $data['grand_tax_type']??'');

		$tax_percent = $xml->createElement('cbc:Percent', $data['grand_tax_percent']??0);
		$tax_baseunit = $xml->createElement('cbc:BaseUnitMeasure', 1);
		$tax_baseunit->setAttribute('unitCode', "C62");
		$tax_per_item = $xml->createElement('cbc:PerUnitAmount', 0);
		$tax_per_item->setAttribute('currencyID', $data['grand_tax_cur']??'');

		$tax_total->appendChild($tax_amount);
		$tax_total->appendChild($tax_subtotal);
		$tax_scheme->appendChild($tax_id_2);
		//$tax_scheme->appendChild($tax_type_code);
		//$tax_category->appendChild($tax_exemption_reason);
		$tax_category->appendChild($tax_id);
		$tax_category->appendChild($tax_scheme);
		$tax_subtotal->appendChild($taxable_amount);
		$tax_subtotal->appendChild($tax_amount2);
		//$tax_subtotal->appendChild($tax_percent);
		//$tax_subtotal->appendChild($tax_baseunit);
		//$tax_subtotal->appendChild($tax_per_item);
		$tax_subtotal->appendChild($tax_category);
		$invoice->appendChild($tax_total);

		//net total
		$legal_monetary_total = $xml->createElement('cac:LegalMonetaryTotal');
		//total excluding tax - but including any discounts and fees
		$total_exclusive_amount = $xml->createElement('cbc:TaxExclusiveAmount', $data['grand_total_no_tax']??0);
		$total_exclusive_amount->setAttribute('currencyID', $data['grand_total_no_tax_cur']??'');
		//total including tax <-this should be the grand total
		$tax_inclusive_amount = $xml->createElement('cbc:TaxInclusiveAmount', $data['grand_total_with_tax']??0);
		$tax_inclusive_amount->setAttribute('currencyID', $data['grand_total_with_tax_cur']??'');
		//total including tax + rounding , but not including deposit/initial payments
		$payable_amount = $xml->createElement('cbc:PayableAmount', $data['grand_total_payable']??0);
		$payable_amount->setAttribute('currencyID', $data['grand_total_payable_cur']??'');
		//total subtotal (only sum of invoice lines)
		$line_ext_amount = $xml->createElement('cbc:LineExtensionAmount', $data['grand_subtotal']??0);
		$line_ext_amount->setAttribute('currencyID', $data['grand_subtotal_cur']??'');
		//total discount
		$allowance_total_amount = $xml->createElement('cbc:AllowanceTotalAmount', $data['grand_discount']??0);
		$allowance_total_amount->setAttribute('currencyID', $data['grand_discount_cur']??'');
		//total fees
		$charge_total_amount = $xml->createElement('cbc:ChargeTotalAmount', $data['grand_fees']??0);
		$charge_total_amount->setAttribute('currencyID', $data['grand_fees_cur']??'');
		//any rounding?
		$payable_rounding_amount = $xml->createElement('cbc:PayableRoundingAmount', $data['grand_rounding']??0);
		$payable_rounding_amount->setAttribute('currencyID', $data['grand_rounding_cur']??'');

		$legal_monetary_total->appendChild($line_ext_amount);
		$legal_monetary_total->appendChild($total_exclusive_amount);
		$legal_monetary_total->appendChild($tax_inclusive_amount);
		$legal_monetary_total->appendChild($allowance_total_amount);
		$legal_monetary_total->appendChild($charge_total_amount);
		$legal_monetary_total->appendChild($payable_rounding_amount);
		$legal_monetary_total->appendChild($payable_amount);
		$invoice->appendChild($legal_monetary_total);

		//invoice line items

		if (!empty($data['item_list'])) {
			foreach ($data['item_list'] as $key => $val) {
				$inv_line = $xml->createElement('cac:InvoiceLine');

				$line_id = $xml->createElement('cbc:ID', $val['id']??'');
				$inv_line->appendChild($line_id);

				$inv_qty = $xml->createElement('cbc:InvoicedQuantity', $val['quantity']??0);
				$inv_qty->setAttribute('unitCode', $val['uom']??'');
				$inv_line->appendChild($inv_qty);

				$line_ext_amount = $xml->createElement('cbc:LineExtensionAmount', $val['total_no_tax']??0);
				$line_ext_amount->setAttribute('currencyID', $val['total_no_tax_cur']??'');
				$inv_line->appendChild($line_ext_amount);

				//allowance charges
				if (isset($val['discount'])) { 
					if (is_array($val['discount'])) {
						foreach ($val['discount'] as $discount_row) {
							$allowance_charge = $xml->createElement('cac:AllowanceCharge');
							$charge_indicator = $xml->createElement('cbc:ChargeIndicator', $discount_row['indicator']??'');
							$charge_reason = $xml->createElement('cbc:AllowanceChargeReason', $discount_row['reason']??'');
							$amount = $xml->createElement('cbc:Amount', $discount_row['amt']??0);
							$amount->setAttribute('currencyID', $discount_row['cur']??'');
							$multiplier_factor_numeric = $xml->createElement('cbc:MultiplierFactorNumeric', $discount_row['percent']??'');

							$allowance_charge->appendChild($charge_indicator);
							$allowance_charge->appendChild($charge_reason);
							$allowance_charge->appendChild($multiplier_factor_numeric);
							$allowance_charge->appendChild($amount);
							$inv_line->appendChild($allowance_charge);

						}
					}
				}

				//if ($val['tax']>0) {

					$tax_total = $xml->createElement('cac:TaxTotal');
					$tax_amount = $xml->createElement('cbc:TaxAmount', $val['tax']??0);
					$tax_amount->setAttribute('currencyID', $val['tax_cur']??'');
					$tax_amount2 = $xml->createElement('cbc:TaxAmount', $val['tax']??0);
					$tax_amount2->setAttribute('currencyID', $val['tax_cur']??'');
					$tax_subtotal = $xml->createElement('cac:TaxSubtotal');
					$taxable_amount = $xml->createElement('cbc:TaxableAmount', $val['taxable']??0);
					$taxable_amount->setAttribute('currencyID', $val['taxable_cur']??'');
					$tax_category = $xml->createElement('cac:TaxCategory');
					$percent = $xml->createElement('cbc:Percent', $val['tax_rate']??0);
					$tax_scheme = $xml->createElement('cac:TaxScheme');
					$tax_id = $xml->createElement('cbc:ID', $val['tax_code']??'');
					$tax_id_2 = $xml->createElement('cbc:ID', 'OTH');
					$tax_id_2->setAttribute('schemeID', "UN/ECE 5153");
					$tax_id_2->setAttribute('schemeAgencyID', "6");
					$tax_type_code = $xml->createElement('cbc:TaxTypeCode', $val['tax_type']??'');
					$tax_exemption_reason = $xml->createElement('cbc:TaxExemptionReason', $val['tax_exemption_reason']??'');

					$tax_percent = $xml->createElement('cbc:Percent', $val['tax_rate']??0);
					$tax_baseunit = $xml->createElement('cbc:BaseUnitMeasure', 1);
					$tax_baseunit->setAttribute('unitCode', "C62");
					$tax_per_item = $xml->createElement('cbc:PerUnitAmount', $val['per_tax']??0);
					$tax_per_item->setAttribute('currencyID', $val['tax_cur']??'');

					$tax_total->appendChild($tax_amount);
					$tax_total->appendChild($tax_subtotal);
					$tax_scheme->appendChild($tax_id_2);
					//$tax_scheme->appendChild($tax_type_code);
					$tax_category->appendChild($tax_id);
					//$tax_category->appendChild($percent);
					//$tax_category->appendChild($tax_exemption_reason);
					$tax_category->appendChild($tax_scheme);
					$tax_subtotal->appendChild($taxable_amount);
					$tax_subtotal->appendChild($tax_amount2);
					$tax_subtotal->appendChild($tax_percent);
					$tax_subtotal->appendChild($tax_baseunit);
					$tax_subtotal->appendChild($tax_per_item);
					$tax_subtotal->appendChild($tax_category);
					$inv_line->appendChild($tax_total);

				//}

				$item = $xml->createElement('cac:Item');

				/*$commodity_classification = $xml->createElement('cac:CommodityClassification');
				$item_classification = $xml->createElement('cbc:ItemClassificationCode', '');
				$item_classification->setAttribute('listID', 'PTC');
				$commodity_classification->appendChild($item_classification);
				$item->appendChild($commodity_classification);*/

				$description = $xml->createElement('cbc:Description', $val['desc']??'');
				$item->appendChild($description);

				$origin_country = $xml->createElement('cac:OriginCountry');
				$idendication_code = $xml->createElement('cbc:IdentificationCode', $val['country_code']??'');
				$origin_country->appendChild($idendication_code);
				$item->appendChild($origin_country);

				if ($consolidated) {
					$commodity_classification = $xml->createElement('cac:CommodityClassification');
					$item_classification = $xml->createElement('cbc:ItemClassificationCode', '004');
					$item_classification->setAttribute('listID', 'CLASS');
					$commodity_classification->appendChild($item_classification);
					$item->appendChild($commodity_classification);
				} else {
					if (isset($val['tariffs'])) {
						if (!empty($val['tariffs'])) {
							foreach ($val['tariffs'] as $tariff) {
								$commodity_classification = $xml->createElement('cac:CommodityClassification');
								$item_classification = $xml->createElement('cbc:ItemClassificationCode', $tariff??'022');
								$item_classification->setAttribute('listID', 'CLASS');
								$commodity_classification->appendChild($item_classification);
								$item->appendChild($commodity_classification);
							}
						} else {
							//if not provided, default go with 022 - OTHERS
							$commodity_classification = $xml->createElement('cac:CommodityClassification');
							$item_classification = $xml->createElement('cbc:ItemClassificationCode', '022');
							$item_classification->setAttribute('listID', 'CLASS');
							$commodity_classification->appendChild($item_classification);
							$item->appendChild($commodity_classification);
						}
					} else {
						//if not provided, default go with 022 - OTHERS
						$commodity_classification = $xml->createElement('cac:CommodityClassification');
						$item_classification = $xml->createElement('cbc:ItemClassificationCode', '022');
						$item_classification->setAttribute('listID', 'CLASS');
						$commodity_classification->appendChild($item_classification);
						$item->appendChild($commodity_classification);
					}
				}

				$inv_line->appendChild($item);

				$price = $xml->createElement('cac:Price');
				$price_amount = $xml->createElement('cbc:PriceAmount', $val['price']??0);
				$price_amount->setAttribute('currencyID', $val['cur']??'');
				$price->appendChild($price_amount);
				$inv_line->appendChild($price);

				$item_price_ext = $xml->createElement('cac:ItemPriceExtension');
				$amount = $xml->createElement('cbc:Amount', $val['subtotal']??0);
				$amount->setAttribute('currencyID', $val['subtotal_cur']??'');

				$item_price_ext->appendChild($amount);
				$inv_line->appendChild($item_price_ext);

				$invoice->appendChild($inv_line);
			}
		}

		$xml->save( $data['file_path'].$data['file_name'] );

		return array('status' => 'succ', 'msg' => '');
	}

	public function do_validation( $data ) {

		$err = array();

		if (empty($data['invoice_num']??'')) {
			$err[] = 'Invoice ID missing.';
		}

		if (empty($data['file_path']??'') && empty($data['file_name']??'')) {
			$err[] = 'File path or name not found.';
		}

		if (empty($data['created_date']??'')) {
			$err[] = 'Created Date missing.';
		}

		if (empty($data['cur']??'')) {
			$err[] = 'Currency code missing.';
		}

		if (empty($data['cur_rate']??'')) {
			$err[] = 'Currency Rate missing.';
		}

		if (empty($data['period_start']??'')) {
			$err[] = 'Period start missing.';
		}

		if (empty($data['period_end']??'')) {
			$err[] = 'Period end missing.';
		}

		if (empty($data['payment_term']??'')) {
			$err[] = 'Payment term missing.';
		}

		return $err;
	}

	public function prep_signature($data) {

		$sign_data = array();
		$sign_data['succ'] = 0;
		$sign_data['msg'] = '';

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->load($data['xml_loc']);

		$cannon = $xml->C14N(false, false);
		//log_message('error', 'CANON XML:'.$cannon);
		$hash = hash('sha256', $cannon);
		$DOCDIGEST = hex_to_base64($hash);
		//log_message('error', 'DOCDIGEST:'.$DOCDIGEST);

		$filename = $data['p12_file'];
		$password = $data['p12_pin'];
		$p12_results = array();
		$worked = openssl_pkcs12_read(file_get_contents($filename), $p12_results, $password);
		if($worked) {
		    //log_message('error', 'CERT LOADED:'.print_r($p12_results, true));
		} else {
		    log_message('error', 'CERT LOAD FAIL:'.openssl_error_string());
		    $sign_data['msg'] = 'CERT LOAD FAIL';
		    return $sign_data;
		}

		//create signature
		openssl_sign($cannon, $signature, $p12_results['pkey'], "RSA-SHA256");
		$SIG = base64_encode($signature);
		log_message('error', 'SIGNATURE:'.$SIG);

		//verify
		//$pubkeyid = openssl_pkey_get_public($p12_results['cert']);
		//$ok = openssl_verify($DOCDIGEST, $signature, $pubkeyid, "sha256WithRSAEncryption");
		//log_message('error', 'VERIFY:'.$ok);

		preg_match('/(?P<begin>-+[^-]+-+)(?P<body>.*?)(?P<end>-+[^-]+-+)/s', $p12_results['cert'], $m);

		$x509 = str_replace("\n", '', $m['body']);

		//log_message('error', 'X509:'.$x509);

		//log_message('error', 'CERT LOADED:'.$x509);
		$X509CertDecoded = base64_decode($x509);
		//log_message('error', 'CERT DECODED:'.$X509CertDecoded);
		$hash = hash('sha256', $X509CertDecoded, true);
		//log_message('error', 'CERTDIGEST HASH:'.$hash);
		$CERTDIGEST = base64_encode($hash);
		//log_message('error', 'CERTDIGEST:'.$CERTDIGEST);

		$x509_parse = openssl_x509_parse(file_get_contents($this->config->item('p12_pem')));

		$issuer_str = 'CN='.$x509_parse['issuer']['CN'].', OU='.$x509_parse['issuer']['OU'].', O='.$x509_parse['issuer']['O'].', C='.$x509_parse['issuer']['C'];

		$sign_data['succ'] = 1;
		$sign_data['xml_loc'] = $data['xml_loc'];
		$sign_data['DOCDIGEST'] = $DOCDIGEST;
		$sign_data['SIG'] = $SIG;
		$sign_data['CERTDIGEST'] = $CERTDIGEST;
		$sign_data['x509'] = $x509;
		$sign_data['x509_issuer'] = $issuer_str;
		$sign_data['x509_serial'] = $x509_parse['serialNumber'];

		return $sign_data;

	}

	public function sign_doc( $data ) {

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->load($data['xml_loc']);
		$xml->loadXML($xml->saveXML());
		//echo $xml->saveXML();
		//log_message('error', 'LOADED XML:'.$xml->saveXML());

		$root=$xml->documentElement->childNodes[0];
		//$root->parentNode->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

		$ubl_extensions = $xml->createElement('ext:UBLExtensions');
		$ubl_extension = $xml->createElement('ext:UBLExtension');
		$extension_uri = $xml->createElement('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');

		$extension_content = $xml->createElement('ext:ExtensionContent');

		$sig_document_signatures = $xml->createElement('sig:UBLDocumentSignatures');
		$sig_document_signatures->setAttribute('xmlns:sig', 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2');
		$sig_document_signatures->setAttribute('xmlns:sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');
		$sig_document_signatures->setAttribute('xmlns:sbc', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2');

		$sac_signature = $xml->createElement('sac:SignatureInformation');
		$sac_signature_id = $xml->createElement('cbc:ID', 'urn:oasis:names:specification:ubl:signature:1');
		$sac_signature_sbc = $xml->createElement('sbc:ReferencedSignatureID', 'urn:oasis:names:specification:ubl:signature:Invoice');

		$ds_signature = $xml->createElement('ds:Signature');
		$ds_signature->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
		$ds_signature->setAttribute('Id', 'signature');

		$signed_info = $xml->createElement('ds:SignedInfo');
		$signed_value = $xml->createElement('ds:SignatureValue', $data['SIG']);
		$key_info = $xml->createElement('ds:KeyInfo');
		$sig_obj = $xml->createElement('ds:Object');

		$canon_method = $xml->createElement('ds:CanonicalizationMethod');
		//CANNON MIGHT BE WRONG
		$canon_method->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
		$sign_method = $xml->createElement('ds:SignatureMethod');
		$sign_method->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
		$ref1 = $xml->createElement('ds:Reference');
		$ref1->setAttribute('Id', 'id-doc-signed-data');
		$ref1->setAttribute('URI', '');
		$ref2 = $xml->createElement('ds:Reference');
		$ref2->setAttribute('Type', 'http://www.w3.org/2000/09/xmldsig#SignatureProperties');
		$ref2->setAttribute('URI', '#id-xades-signed-props');

		$ref1_transform = $xml->createElement('ds:Transforms');
		$ref1_digestmethod = $xml->createElement('ds:DigestMethod');
		$ref1_digestmethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
		$ref1_digestvalue = $xml->createElement('ds:DigestValue', $data['DOCDIGEST']);

		$transform1 = $xml->createElement('ds:Transform');
		$transform1->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
		$transform2 = $xml->createElement('ds:Transform');
		$transform2->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
		$transform3 = $xml->createElement('ds:Transform');
		$transform3->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');

		$xpath1 = $xml->createElement('ds:XPath', 'not(//ancestor-or-self::ext:UBLExtensions)');
		$xpath2 = $xml->createElement('ds:XPath', 'not(//ancestor-or-self::cac:Signature)');

		$ref2_digestmethod = $xml->createElement('ds:DigestMethod');
		$ref2_digestmethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
		$ref2_digestvalue = $xml->createElement('ds:DigestValue', '-');

		$x509_data = $xml->createElement('ds:X509Data');
		$x509_cert = $xml->createElement('ds:X509Certificate', $data['x509']);

		$xades_qualify = $xml->createElement('xades:QualifyingProperties');
		$xades_qualify->setAttribute('xmlns:xades', 'http://uri.etsi.org/01903/v1.3.2#');
		$xades_qualify->setAttribute('Target', 'signature');

		$xades_signed_properties = $xml->createElement('xades:SignedProperties');
		$xades_signed_properties->setAttribute('Id', 'id-xades-signed-props');

		$xades_signed_sig = $xml->createElement('xades:SignedSignatureProperties');

		$sign_time = gmdate('Y-m-d').'T'.gmdate('H:i:s').'Z';

		$xades_time = $xml->createElement('xades:SigningTime', $sign_time);

		$xades_sign_cert = $xml->createElement('xades:SigningCertificate');

		$xades_cert = $xml->createElement('xades:Cert');

		$xades_certdigest = $xml->createElement('xades:CertDigest');
		$xades_issuer = $xml->createElement('xades:IssuerSerial');

		$xades_digestmethod = $xml->createElement('ds:DigestMethod');
		$xades_digestmethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
		$xades_digestvalue = $xml->createElement('ds:DigestValue', $data['CERTDIGEST']);

		$xades_issuer_str = $xml->createElement('ds:X509IssuerName', $data['x509_issuer']);
		$xades_serial = $xml->createElement('ds:X509SerialNumber', $data['x509_serial']);

		$xades_issuer->appendChild($xades_issuer_str);
		$xades_issuer->appendChild($xades_serial);

		$xades_certdigest->appendChild($xades_digestmethod);
		$xades_certdigest->appendChild($xades_digestvalue);

		$xades_cert->appendChild($xades_certdigest);
		$xades_cert->appendChild($xades_issuer);

		$xades_sign_cert->appendChild($xades_cert);

		$xades_signed_sig->appendChild($xades_time);
		$xades_signed_sig->appendChild($xades_sign_cert);

		$xades_signed_properties->appendChild($xades_signed_sig);

		$xades_qualify->appendChild($xades_signed_properties);

		$sig_obj->appendChild($xades_qualify);

		$x509_data->appendChild($x509_cert);
		$key_info->appendChild($x509_data);

		$ref2->appendChild($ref2_digestmethod);
		$ref2->appendChild($ref2_digestvalue);

		$transform1->appendChild($xpath1);
		$transform2->appendChild($xpath2);

		$ref1_transform->appendChild($transform1);
		$ref1_transform->appendChild($transform2);
		$ref1_transform->appendChild($transform3);

		$ref1->appendChild($ref1_transform);
		$ref1->appendChild($ref1_digestmethod);
		$ref1->appendChild($ref1_digestvalue);

		$signed_info->appendChild($canon_method);
		$signed_info->appendChild($sign_method);
		$signed_info->appendChild($ref1);
		$signed_info->appendChild($ref2);

		$ds_signature->appendChild($signed_info);
		$ds_signature->appendChild($signed_value);
		$ds_signature->appendChild($key_info);
		$ds_signature->appendChild($sig_obj);

		$sac_signature->appendChild($sac_signature_id);
		$sac_signature->appendChild($sac_signature_sbc);
		$sac_signature->appendChild($ds_signature);

		$sig_document_signatures->appendChild($sac_signature);

		$extension_content->appendChild($sig_document_signatures);

		$ubl_extension->appendChild($extension_uri);
		$ubl_extension->appendChild($extension_content);
		$ubl_extensions->appendChild($ubl_extension);

		$xml->documentElement->insertBefore($ubl_extensions,$root);

		//log_message('error', 'LOADED XML:'.print_r($root, true));

		$xml->save( $data['file_path'].$data['signed_file_name'] );

		//PropDigest
		$xml2 = new DOMDocument('1.0', 'utf-8');
		$xml2->load($data['file_path'].$data['signed_file_name']);
		
		//log_message('error', 'NEW XML:'.$xml2->saveXML());

		$xmlString = preg_replace("/<\\?xml.*\\?>/",'',$xml2->saveXML(),1);
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->loadXML($xmlString);
		$doc->preserveWhiteSpace = true;
		$xpath = new DOMXPath($doc);
       	$xpath->registerNamespace('xmlns', "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2");
       	$xpath->registerNamespace('sig', "urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2");
       	$xpath->registerNamespace('sac', "urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2");
       	$xpath->registerNamespace('sbc', "urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2");
        $xpath->registerNamespace('ds', "http://www.w3.org/2000/09/xmldsig#");
       	$xpath->registerNamespace('xades', "http://uri.etsi.org/01903/v1.3.2#");
		//$filtered = $xpath->query("/*[local-name()='Invoice']/*[local-name()='UBLExtensions']/*[local-name()='UBLExtension']/*[local-name()='ExtensionContent']/*[local-name()='UBLDocumentSignatures']/*[local-name()='SignatureInformation']/*[local-name()='Signature']/*[local-name()='Object']/*[local-name()='QualifyingProperties']/*[local-name()='SignedProperties']");
		$SignedProperties = "//xmlns:Invoice/ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sig:UBLDocumentSignatures/sac:SignatureInformation/ds:Signature/ds:Object/xades:QualifyingProperties/xades:SignedProperties";
		$filtered = $xpath->query($SignedProperties);

		//$signed_str = $filtered[0]->C14N(false, false);
		//log_message('error', 'CANON XML:'.$canonicalizationInvoiceXML);
		//$signed_str = str_replace('></ds:DigestMethod>', '/>', $canonicalizationInvoiceXML);

	    //foreach ($filtered as $node) {
			//ob_start();
			//var_dump($node);
			//$mystring = ob_get_clean();

			//log_message('error', 'FILTERED:'.$node->textContent);
			//$signed_str = str_replace(" ", "", $node->textContent);

			$resultNode = $filtered->item(0);
			$newDom = new DOMDocument('1.0', 'utf-8');
			$newDom->appendChild($newDom->importNode($resultNode,1));
			
			/*
			$signed_str = preg_replace("/<\\?xml.*\\?>/",'',$newDom->saveXML(),1);
			*/
			
			//$signed_str = $newDom->saveXML();
			$cannon = $newDom->C14N(true, false);
			//absolute insanity
			$signed_str = str_replace('<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="id-xades-signed-props">', '<xades:SignedProperties Id="id-xades-signed-props" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#">', $cannon);
			$signed_str = str_replace('xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"', 'Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"', $signed_str);
			$signed_str = trim($signed_str);

	    //}

		/*$xml2_str = $xml2->saveXML();

		$split1 = explode(' Target="signature">', $xml2_str);
		$split2 = explode('</xades:QualifyingProperties>', $split1[1]);

		$signed_str = $split2[0];*/

		log_message('error', 'CUT XML:'.$signed_str);

		//$signed_str_without_spaces = str_replace(' ', '', $signed_str);

		//log_message('error', 'CUT XML WITHOUT SPACES:'.$signed_str_without_spaces);

		$hash = hash('sha256', $signed_str, true);
		$PROPDIGEST = base64_encode($hash);

		log_message('error', 'PROPDIGEST:'.$PROPDIGEST);

		$root=$xml2->documentElement->childNodes[0];
		$ubl=$root->childNodes[0];
		$extension_content=$ubl->childNodes[1];
		$sig=$extension_content->childNodes[0];
		$sac=$sig->childNodes[0];
		$ds_sig=$sac->childNodes[2];
		$signed_info=$ds_sig->childNodes[0];
		$ref=$signed_info->childNodes[3];
		$props_digest=$ref->childNodes[1];
		$props_digest->textContent = $PROPDIGEST;

		//$accounting_sup_party = $xml2->getElementsByTagName('cac:AccountingSupplierParty');
		$accounting_sup_party = '';
		foreach ($xml2->documentElement->childNodes as $node) {
			if ($node->tagName == "cac:AccountingSupplierParty") {
				$accounting_sup_party = $node;
				break;
			}
		}

		$cac_signature = $xml2->createElement('cac:Signature');
		$cac_signature_id = $xml2->createElement('cbc:ID', 'urn:oasis:names:specification:ubl:signature:Invoice');
		$cac_sign_method = $xml2->createElement('cbc:SignatureMethod', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');
		$cac_signature->appendChild($cac_signature_id);
		$cac_signature->appendChild($cac_sign_method);

		$xml2->documentElement->insertBefore($cac_signature,$accounting_sup_party);

		$xml2->save( $data['file_path'].$data['signed_file_name'] );

		$xml3 = new DOMDocument('1.0', 'utf-8');
		$xml3->load( $data['file_path'].$data['signed_file_name'] );
		$xmlString = preg_replace("/<\\?xml.*\\?>/",'',$xml3->saveXML(),1);
		file_put_contents($data['file_path'].$data['signed_file_name'], trim($xmlString));

		$xml4 = new DOMDocument;
		$xml4->load( $data['file_path'].$data['signed_file_name'] );
		$xmlString = preg_replace("/<\\?xml.*\\?>/",'',$xml4->saveXML(),1);

		$xmlString = str_replace("/><ds:DigestValue>", "></ds:DigestMethod><ds:DigestValue>", $xmlString);
		file_put_contents($data['file_path'].$data['signed_file_name'], trim($xmlString));

		//log_message('error', 'SELECT:'.print_r($accounting_sup_party, true));

		return array('status' => 'succ', 'msg' => '');

	}

	public function sign_doc_node( $data ) {

        if (empty($data['filename']) || empty($data['output'])) {
            return array(false, 'did not specify filename or output file');
        }

        if (empty($data['p12_file']) || empty($data['p12_pin']) || empty($data['sig_pem'])) {
        	return array(false, 'digital signature file details incomplete');
        }

        $cmd = NODE_PATH.'node node/digital_sign.js --p12_file_name="'.$data['p12_file'].'" --p12_pin="'.$data['p12_pin'].'" --xml_file="'.$data['filename'].'" --pem_file="'.$data['sig_pem'].'" --output_file="'.$data['output'].'"';
        log_message('error', $cmd);
        $this->execCmdWait($cmd);
        return array(true, '');

	}

	public function execCmdWait($cmd) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return shell_exec("$cmd");
		} else {
			return shell_exec("$cmd 2>/dev/null");
		}	
	}

}