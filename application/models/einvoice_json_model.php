<?php
class Einvoice_json_model extends MY_Model{

	protected $_table 			= 'invoice_head';
	protected $_detail 			= 'invoice_details';
	protected $_primary_key 	= 'invoice_id';
	protected $_primary_ke_2	= 'invoice_detail_id';
	protected $_fkey 			= 'cust_id';
	protected $_unique_key 		= 'invoice_num';

	public function __construct()
	{
        parent::__construct();
	}

	public function generate_json_by_inv_id( $data, $consolidated=false )
	{	

		//mandotory validation
		$err = $this->do_validation( $data );

		if (!empty($err)) {
			return array('status' => 'err', 'msg' => implode("\n\r", $err));
		}

		$json = array();
		$json['_D'] = "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2";
		$json['_A'] = "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2";
		$json['_B'] = "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2";
		//$json['_E'] = "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2";
		//$json['Invoice'] = $json['Invoice'];

		//$json['Invoice'] = array();
		$json['Invoice'][0]['ID'] = array(0 => array("_" => $data['invoice_num']));
		$json['Invoice'][0]['IssueDate'] = array(0 => array("_" => gmdate("Y-m-d")));
		$json['Invoice'][0]['IssueTime'] = array(0 => array("_" => gmdate("H:i:s")."Z"));
		$json['Invoice'][0]['InvoiceTypeCode'] = array(0 => array("_" => $data['xml_type'], "listVersionID" => $data['version']));
		$json['Invoice'][0]['DocumentCurrencyCode'] = array(0 => array("_" => $data['cur']));
		$json['Invoice'][0]['TaxCurrencyCode'] = array(0 => array("_" => $data['cur']));
		$json['Invoice'][0]['InvoicePeriod'][0]["StartDate"] = array(0 => array("_" => $data['period_start']));
		$json['Invoice'][0]['InvoicePeriod'][0]["EndDate"] = array(0 => array("_" => $data['period_end']));
		$json['Invoice'][0]['InvoicePeriod'][0]["Description"] = array(0 => array("_" => $data['payment_term']??''));

		//if credit or debit note, need to attached reference
		if ((isset($data['main_doc'])) && ( ($data['xml_type'] == '02') || ($data['xml_type'] == '03') ) ) {
			//from myinvois
			$json['Invoice'][0]['BillingReference'][0]['InvoiceDocumentReference'][0]['ID'] = array(0 => array("_" => $data['main_doc']['id']));
			$json['Invoice'][0]['BillingReference'][0]['InvoiceDocumentReference'][0]['UUID'] = array(0 => array("_" => $data['main_doc']['uuid']));
			$json['Invoice'][0]['BillingReference'][0]['AdditionalDocumentReference'][0]['ID'] = array(0 => array("_" => $data['invoice_num']));
		} else {
			//from myinvois
			$json['Invoice'][0]['BillingReference'][0]['InvoiceDocumentReference'][0]['ID'] = array(0 => array("_" => $data['invoice_num']));
			//$json['Invoice'][0]['BillingReference'][0]['InvoiceDocumentReference'][0]['UUID'] = array(0 => array("_" => "UUID from Myinvois"));
			$json['Invoice'][0]['BillingReference'][0]['AdditionalDocumentReference'][0]['ID'] = array(0 => array("_" => $data['invoice_num']));
		}

		//additional doc ref
		if (isset($data['additional_doc'])) {
			if (is_array($data['additional_doc'])) {
				if (!empty($data['additional_doc'])) {
					$cnt = 0;
					foreach ($data['additional_doc'] as $doc_row) {
						$json['Invoice'][0]['AdditionalDocumentReference'][$cnt]['ID'] = array(0 => array("_" => $doc_row['id']??''));
						$json['Invoice'][0]['AdditionalDocumentReference'][$cnt]['DocumentType'] = array(0 => array("_" => $doc_row['doctype']??''));
						$json['Invoice'][0]['AdditionalDocumentReference'][$cnt]['DocumentDescription'] = array(0 => array("_" => $doc_row['desc']??''));
						if (isset($doc_row['attachment'])) {
							if (is_array($doc_row['attachment'])) {
								$acnt = 0;
								foreach ($doc_row['attachment'] as $attachment) {
									$json['Invoice'][0]['AdditionalDocumentReference'][$cnt]['Attachment'][$acnt]['EmbeddedDocumentBinaryObject'] = array(0 => array("_" =>$attachment['file']??'', "mimeCode" => $attachment['mimetype']??''));
									$acnt++;
								}
							}
						}
						$cnt++;
					}
				}
			}
		}

		//supplier
		if (!empty($data['supplier'])) {
			$json['Invoice'][0]["AccountingSupplierParty"][0]["AdditionalAccountID"] = array(0 => array("_" => $data['supplier']['agency_no']??'', "schemeAgencyName" => $data['supplier']['agency']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["IndustryClassificationCode"] = array(0 => array("_" => $data['supplier']['msic']??'', "name" => $data['supplier']['msic_desc']??''));

			$pcnt = 0;
			if (!empty($data['supplier']['tin'])) {
				$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['supplier']['tin'], "schemeID" => "TIN"));
				$pcnt++;
			}
			if (!empty($data['supplier']['company_reg_id']) && !empty($data['supplier']['company_reg_type'])) {
				$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['supplier']['company_reg_id'], "schemeID" => $data['supplier']['company_reg_type']));
				$pcnt++;
			}
			if (!empty($data['supplier']['tax_id'])) {
				$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['supplier']['tax_id'], "schemeID" => "SST"));
				$pcnt++;
			}
			if (!empty($data['supplier']['ttx_id'])) {
				$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['supplier']['ttx_id'], "schemeID" => "TTX"));
				$pcnt++;
			}

			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["CityName"] = array(0 => array("_" => $data['supplier']['city_name']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["PostalZone"] = array(0 => array("_" => $data['supplier']['postal_zone']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["CountrySubentityCode"] = array(0 => array("_" => $data['supplier']['state_code']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][0]['Line'] = array(0 => array("_" => $data['supplier']['line_1']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][1]['Line'] = array(0 => array("_" => $data['supplier']['line_2']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][2]['Line'] = array(0 => array("_" => $data['supplier']['line_3']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PostalAddress"][0]["Country"][0]["IdentificationCode"] = array(0 => array("_" => $data['supplier']['country_code']??'', "listID" => "ISO3166-1", "listAgencyID" => "6"));

			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["PartyLegalEntity"][0]["RegistrationName"] = array(0 => array("_" => $data['supplier']['company_name']??''));

			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["Contact"][0]["Telephone"] = array(0 => array("_" => $data['supplier']['tel']??''));
			$json['Invoice'][0]["AccountingSupplierParty"][0]["Party"][0]["Contact"][0]["ElectronicMail"] = array(0 => array("_" => $data['supplier']['email']??''));
		}

		//buyer
		if ($consolidated) {

			$json['Invoice'][0]["AccountingCustomerParty"][0]["AdditionalAccountID"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["IndustryClassificationCode"] = array(0 => array("_" => "NA", "name" => "NA"));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][0]["ID"] = array(0 => array("_" => "EI00000000010", "schemeID" => "TIN"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][1]["ID"] = array(0 => array("_" => "NA", "schemeID" => "NRIC"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][2]["ID"] = array(0 => array("_" => "NA", "schemeID" => "SST"));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["CityName"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["PostalZone"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["CountrySubentityCode"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][0]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][1]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][2]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["Country"][0]["IdentificationCode"] = array(0 => array("_" => "NA", "listID" => "ISO3166-1", "listAgencyID" => "6"));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyLegalEntity"][0]["RegistrationName"] = array(0 => array("_" => "General Public"));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["Contact"][0]["Telephone"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["Contact"][0]["ElectronicMail"] = array(0 => array("_" => "NA"));

		} else if (!empty($data['buyer'])) {

			$json['Invoice'][0]["AccountingCustomerParty"][0]["AdditionalAccountID"] = array(0 => array("_" => $data['buyer']['agency_no']??'', "schemeAgencyName" => $data['buyer']['agency']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["IndustryClassificationCode"] = array(0 => array("_" => $data['buyer']['msic']??'', "name" => $data['buyer']['msic_desc']??''));

			$pcnt = 0;
			if (!empty($data['buyer']['tin'])) {
				$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['buyer']['tin'], "schemeID" => "TIN"));
				$pcnt++;
			}
			if (!empty($data['buyer']['company_reg_id']) && !empty($data['buyer']['company_reg_type'])) {
				$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['buyer']['company_reg_id'], "schemeID" => $data['buyer']['company_reg_type']));
				$pcnt++;
			}
			if (!empty($data['buyer']['tax_id'])) {
				$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['buyer']['tax_id'], "schemeID" => "SST"));
				$pcnt++;
			}
			if (!empty($data['buyer']['ttx_id'])) {
				$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['buyer']['ttx_id'], "schemeID" => "TTX"));
				$pcnt++;
			}

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["CityName"] = array(0 => array("_" => $data['buyer']['city_name']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["PostalZone"] = array(0 => array("_" => $data['buyer']['postal_zone']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["CountrySubentityCode"] = array(0 => array("_" => $data['buyer']['state_code']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][0]['Line'] = array(0 => array("_" => $data['buyer']['line_1']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][1]['Line'] = array(0 => array("_" => $data['buyer']['line_2']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["AddressLine"][2]['Line'] = array(0 => array("_" => $data['buyer']['line_3']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PostalAddress"][0]["Country"][0]["IdentificationCode"] = array(0 => array("_" => $data['buyer']['country_code']??'', "listID" => "ISO3166-1", "listAgencyID" => "6"));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["PartyLegalEntity"][0]["RegistrationName"] = array(0 => array("_" => $data['buyer']['company_name']??''));

			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["Contact"][0]["Telephone"] = array(0 => array("_" => $data['buyer']['tel']??''));
			$json['Invoice'][0]["AccountingCustomerParty"][0]["Party"][0]["Contact"][0]["ElectronicMail"] = array(0 => array("_" => $data['buyer']['email']??''));


		}

		//delivery
		if ($consolidated) {

			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][0]["ID"] = array(0 => array("_" => "EI00000000010", "schemeID" => "TIN"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][1]["ID"] = array(0 => array("_" => "NA", "schemeID" => "NRIC"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][2]["ID"] = array(0 => array("_" => "NA", "schemeID" => "SST"));

			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["CityName"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["PostalZone"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["CountrySubentityCode"] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][0]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][1]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][2]['Line'] = array(0 => array("_" => "NA"));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["Country"][0]["IdentificationCode"] = array(0 => array("_" => "NA", "listID" => "ISO3166-1", "listAgencyID" => "6"));

			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyLegalEntity"][0]["RegistrationName"] = array(0 => array("_" => "General Public"));

		} else if (!empty($data['delivery'])) {

			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyLegalEntity"][0]["RegistrationName"] = array(0 => array("_" => $data['delivery']['company_name']??''));

			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["CityName"] = array(0 => array("_" => $data['delivery']['city_name']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["PostalZone"] = array(0 => array("_" => $data['delivery']['postal_zone']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["CountrySubentityCode"] = array(0 => array("_" => $data['delivery']['state_code']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][0]['Line'] = array(0 => array("_" => $data['delivery']['line_1']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][1]['Line'] = array(0 => array("_" => $data['delivery']['line_2']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["AddressLine"][2]['Line'] = array(0 => array("_" => $data['delivery']['line_3']??''));
			$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PostalAddress"][0]["Country"][0]["IdentificationCode"] = array(0 => array("_"=> $data['delivery']['country_code']??'', "listID" => "ISO3166-1", "listAgencyID" => "6"));

			$pcnt = 0;
			if (!empty($data['delivery']['tin'])) {
				$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['delivery']['tin'], "schemeID" => "TIN"));
				$pcnt++;
			}
			if (!empty($data['delivery']['company_reg_id']) && !empty($data['delivery']['company_reg_type'])) {
				$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['delivery']['company_reg_id'], "schemeID" => $data['delivery']['company_reg_type']));
				$pcnt++;
			}
			if (!empty($data['delivery']['tax_id'])) {
				$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['delivery']['tax_id'], "schemeID" => "SST"));
				$pcnt++;
			}
			if (!empty($data['delivery']['ttx_id'])) {
				$json['Invoice'][0]["Delivery"][0]["DeliveryParty"][0]["PartyIdentification"][$pcnt]["ID"] = array(0 => array("_" => $data['delivery']['ttx_id'], "schemeID" => "TTX"));
				$pcnt++;
			}

			if (isset($data['delivery']['freight_charges'])) {
				if (isset($data['delivery']['freight_charges']['amt'])) {
					$json['Invoice'][0]["Delivery"][0]["Shipment"][0]["ID"] = array(0 => array("_" => $data['delivery']['freight_charges']['ref']??''));
					$json['Invoice'][0]["Delivery"][0]["Shipment"][0]["FreightAllowanceCharge"][0]["ChargeIndicator"] = array(0 => array("_" => true));
					$json['Invoice'][0]["Delivery"][0]["Shipment"][0]["FreightAllowanceCharge"][0]["AllowanceChargeReason"] = array(0 => array("_" => $data['delivery']['freight_charges']['reason']??''));
					$json['Invoice'][0]["Delivery"][0]["Shipment"][0]["FreightAllowanceCharge"][0]["Amount"] = array(0 => array("_" => $data['delivery']['freight_charges']['amt']??0, "currencyID" => $data['delivery']['freight_charges']['cur']??''));
				}
			}

		}

		$json['Invoice'][0]['PaymentMeans'][0]["PaymentMeansCode"] = array(0 => array("_" => $data['payment_method']??''));
		$json['Invoice'][0]['PaymentMeans'][0]["PayeeFinancialAccount"][0]["ID"] = array(0 => array("_" => $data['payment_acc']??''));

		$json['Invoice'][0]['PaymentTerms'][0]["Note"] = array(0 => array("_" => $data['payment_term']));

		if (isset($data['prepaid'])) {

			if (!empty($data['prepaid']['id'])) {
				$json['Invoice'][0]['PrepaidPayment'][0]["ID"] = array(0 => array("_" => $data['prepaid']['id']??''));
				$json['Invoice'][0]['PrepaidPayment'][0]["PaidAmount"] = array(0 => array("_" => $data['prepaid']['amt']??'', "currencyID" => $data['prepaid']['cur']??''));

				if (!empty($data['prepaid']['date'])) {
					$prepaid_date = gmdate('Y-m-d', strtotime($data['prepaid']['date']));
					$prepaid_time = gmdate('H:i:s', strtotime($data['prepaid']['date'])).'Z';
				} else {
					$prepaid_date = '';
					$prepaid_time = '';
				}

				$json['Invoice'][0]['PrepaidPayment'][0]["PaidDate"] = array(0 => array("_" => $prepaid_date));
				$json['Invoice'][0]['PrepaidPayment'][0]["PaidTime"] = array(0 => array("_" => $prepaid_time));
			}

		}

		if (isset($data['allowance'])) { 
			if (is_array($data['allowance'])) {
				$cnt = 0;
				foreach ($data['allowance'] as $allowance_row) {
					$json['Invoice'][0]['AllowanceCharge'][$cnt]["ChargeIndicator"] = array(0 => array("_" => $allowance_row['indicator']??''));
					$json['Invoice'][0]['AllowanceCharge'][$cnt]["AllowanceChargeReason"] = array(0 => array("_" => $allowance_row['reason']??''));
					$json['Invoice'][0]['AllowanceCharge'][$cnt]["Amount"] = array(0 => array("_" => $allowance_row['amt']??0, "currencyID" => $allowance_row['cur']??''));
					$cnt++;
				}
			}
		}

		//tax total
		$json['Invoice'][0]['TaxTotal'][0]['TaxAmount'] = array(0 => array("_" => floatval($data['grand_tax'])??0, "currencyID" => $data['grand_tax_cur']??''));
		$json['Invoice'][0]['TaxTotal'][0]['TaxSubtotal'][0]['TaxableAmount'] = array(0 => array("_" => floatval($data['grand_taxable'])??0, "currencyID" => $data['grand_taxable_cur']??''));
		$json['Invoice'][0]['TaxTotal'][0]['TaxSubtotal'][0]['TaxAmount'] = array(0 => array("_" => floatval($data['grand_tax'])??0, "currencyID" => $data['grand_tax_cur']??''));
		$json['Invoice'][0]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]["ID"] = array(0 => array("_" => $data['grand_tax_code']??''));
		$json['Invoice'][0]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]["TaxScheme"][0]["ID"] = array(0 => array("_" => "OTH", "schemeID" => "UN/ECE 5153", "schemeAgencyID" => "6"));  

		//net total
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['LineExtensionAmount'] = array(0 => array("_" => floatval($data['grand_subtotal'])??0, "currencyID" => $data['grand_subtotal_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['TaxExclusiveAmount'] = array(0 => array("_" => floatval($data['grand_total_no_tax'])??0, "currencyID" => $data['grand_total_no_tax_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['TaxInclusiveAmount'] = array(0 => array("_" => floatval($data['grand_total_with_tax'])??0, "currencyID" => $data['grand_total_with_tax_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['AllowanceTotalAmount'] = array(0 => array("_" => floatval($data['grand_discount'])??0, "currencyID" => $data['grand_discount_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['ChargeTotalAmount'] = array(0 => array("_" => floatval($data['grand_fees'])??0, "currencyID" => $data['grand_fees_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['PayableRoundingAmount'] = array(0 => array("_" => floatval($data['grand_rounding'])??0, "currencyID" => $data['grand_rounding_cur']??''));
		$json['Invoice'][0]['LegalMonetaryTotal'][0]['PayableAmount'] = array(0 => array("_" => floatval($data['grand_total_payable'])??0, "currencyID" => $data['grand_total_payable_cur']??''));

		//invoice lines
		if (!empty($data['item_list'])) {
			$cnt = 0;
			foreach ($data['item_list'] as $key => $val) {
				$json['Invoice'][0]['InvoiceLine'][$cnt]['ID'] = array(0 => array("_" => $val['id']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['InvoicedQuantity'] = array(0 => array("_" => intval($val['quantity'])??0, "unitCode" => $val['uom']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['LineExtensionAmount'] = array(0 => array("_" => floatval($val['total_no_tax'])??0, "currencyID" => $val['total_no_tax_cur']??''));

				//allowance charges
				if (isset($val['discount'])) { 
					if (is_array($val['discount'])) {
						$dcnt = 0;
						foreach ($val['discount'] as $discount_row) {
							$json['Invoice'][0]['InvoiceLine'][$cnt]['AllowanceCharge'][$dcnt]['ChargeIndicator'] = array(0 => array("_" => $discount_row['indicator']??''));
							$json['Invoice'][0]['InvoiceLine'][$cnt]['AllowanceCharge'][$dcnt]['AllowanceChargeReason'] = array(0 => array("_" => $discount_row['reason']??''));
							$json['Invoice'][0]['InvoiceLine'][$cnt]['AllowanceCharge'][$dcnt]['MultiplierFactorNumeric'] = array(0 => array("_" => $discount_row['percent']??''));
							$json['Invoice'][0]['InvoiceLine'][$cnt]['AllowanceCharge'][$dcnt]['Amount'] = array(0 => array("_" => $discount_row['amt']??0, "currencyID" => $val['cur']??''));
							$dcnt++;
						}
					}
				}

				$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxAmount'] = array(0 => array("_" => floatval($val['tax'])??0, "currencyID" => $val['tax_cur']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxableAmount'] = array(0 => array("_" => floatval($val['taxable'])??0, "currencyID" => $val['taxable_cur']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxAmount'] = array(0 => array("_" => floatval($val['tax'])??0, "currencyID" => $val['tax_cur']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]['ID'] = array(0 => array("_" => $val['tax_code']??''));
				//$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]['Percent'] = array(0 => array("_" => floatval($val['tax_rate'])??0));
				//$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]['TaxExemptionReason'] = array(0 => array("_" => $val['tax_exemption_reason']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['TaxTotal'][0]['TaxSubtotal'][0]['TaxCategory'][0]['TaxScheme'][0]['ID'] = array(0 => array("_" => "OTH", "schemeID" => "UN/ECE 5153", "schemeAgencyID" => "6"));

				//item
				$json['Invoice'][0]['InvoiceLine'][$cnt]['Item'][0]['CommodityClassification'][0]['ItemClassificationCode'] = array(0 => array("_" => $val['tariff_code']??'', "listID" => $val['tariff_type']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['Item'][0]['Description'] = array(0 => array("_" => $val['desc']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['Item'][0]['OriginCountry'][0]['IdentificationCode'] = array(0 => array("_" => $val['country_code']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['Price'][0]['PriceAmount'] = array(0 => array("_" => floatval($val['price'])??0, "currencyID" => $val['cur']??''));
				$json['Invoice'][0]['InvoiceLine'][$cnt]['ItemPriceExtension'][0]['Amount'] = array(0 => array("_" => floatval($val['subtotal'])??0, "currencyID" => $val['subtotal_cur']??''));

				$cnt++;
			}
		}

		//save json in to file
		file_put_contents($data['file_path'].$data['file_name'], json_encode($json));

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

		//cleanup the json str , remove backslashes , convert any utf8 characters
		$json_str = file_get_contents($data['json_loc']);
		$json_str = transliterator_transliterate("Hex-Any/Java", $json_str);
		$json_str = stripslashes($json_str);
		//log_message('error', 'JSON:'.$json_str);
		$hash = hash('sha256', $json_str);

		$DOCDIGEST = hex_to_base64($hash);
		//self explanatory, just hash and hex_to_base64 the json string
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
		//https://stackoverflow.com/questions/65095389/how-can-i-get-php-to-sign-an-input-exactly-the-same-as-c
		//openssl_sign does not expect a hash but the actual data itself
		openssl_sign($json_str, $signature, $p12_results['pkey'], 'RSA-SHA256');
		$SIG = base64_encode($signature);
		log_message('error', 'SIGNATURE:'.$SIG);

		//verify
		//$pubkeyid = openssl_pkey_get_public($p12_results['cert']);
		//$ok = openssl_verify($json_str, $signature, file_get_contents($this->config->item('p12_pub')), "sha256WithRSAEncryption");
		//log_message('error', 'VERIFY:'.$ok);

		//take the cert generated by openssl_pkcs12_read and remove the BEGIN AND END lines and newlines (\n)
		preg_match('/(?P<begin>-+[^-]+-+)(?P<body>.*?)(?P<end>-+[^-]+-+)/s', $p12_results['cert'], $m);

		//then this will be the x509 value
		$x509 = str_replace("\n", '', $m['body']);

		//log_message('error', 'X509:'.$x509);

		//log_message('error', 'CERT LOADED:'.$x509);
		$X509CertDecoded = base64_decode($x509);
		//log_message('error', 'CERT DECODED:'.$X509CertDecoded);
		$hash = hash('sha256', $X509CertDecoded, true);
		//log_message('error', 'CERTDIGEST HASH:'.$hash);
		$CERTDIGEST = base64_encode($hash);
		//for CERTDIGEST, need to decode the x509 returned by openssl back to binary form and then hash it and encode again
		log_message('error', 'CERTDIGEST:'.$CERTDIGEST);

		//for now, need to do a parse from a pem file to get issuer and subject contents
		$x509_parse = openssl_x509_parse(file_get_contents($data['p12_pem']));

		$issuer_str = 'CN='.$x509_parse['issuer']['CN'].', OU='.$x509_parse['issuer']['OU'].', O='.$x509_parse['issuer']['O'].', C='.$x509_parse['issuer']['C'];

		$subject_str = 'CN='.$x509_parse['subject']['CN'].', O='.$x509_parse['subject']['O'].', C='.$x509_parse['subject']['C'];

		$sign_data['succ'] = 1;
		$sign_data['json_loc'] = $data['json_loc'];
		$sign_data['DOCDIGEST'] = $DOCDIGEST;
		$sign_data['SIG'] = $SIG;
		$sign_data['CERTDIGEST'] = $CERTDIGEST;
		$sign_data['x509'] = $x509;
		$sign_data['x509_issuer'] = $issuer_str;
		$sign_data['x509_subject'] = $subject_str;
		$sign_data['x509_serial'] = $x509_parse['serialNumber'];

		return $sign_data;
	}

	public function sign_doc( $data ) {

		$json_str = file_get_contents($data['json_loc']);
		$json = json_decode($json_str, true);

		//log_message('error', print_r($json, true));

		//$test = json_encode($json);

		//log_message('error', $test);

		$digestmethod = array(0 => array("_" => "", "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"));
		$digestvalue = array(0 => array("_" => $data['DOCDIGEST']));

		$digestmethod2 = array(0 => array("_" => "", "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"));

		$sign_value = array(0 => array("_" => $data['SIG']));

		$sign_time = gmdate('Y-m-d').'T'.gmdate('H:i:s').'Z';
		/*$sign_property = array();
		$sign_property[0]['SignedSignatureProperties'][0]['SigningTime'] = array(0 => array("_", $sign_time));
		$sign_property[0]['SignedSignatureProperties'][0]['SigningCertificate'][0]['Cert'][0]['CertDigest'][0]['DigestMethod'] = array(0 => array("_" => "", "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"));
		$sign_property[0]['SignedSignatureProperties'][0]['SigningCertificate'][0]['Cert'][0]['CertDigest'][0]['DigestValue'] = array(0 => array("_" => $data['CERTDIGEST']));
		$sign_property[0]['SignedSignatureProperties'][0]['SigningCertificate'][0]['Cert'][0]['IssuerSerial'][0]['X509IssuerName'] = array(0 => array("_" => $data['x509_issuer']));
		$sign_property[0]['SignedSignatureProperties'][0]['SigningCertificate'][0]['Cert'][0]['IssuerSerial'][0]['X509SerialNumber'] = array(0 => array("_" => $data['x509_serial']));*/

		$keyinfo = array();
		$keyinfo[0]['X509Data'][0]['X509Certificate'] = array(0 => array("_" => $data['x509']));
		$keyinfo[0]['X509Data'][0]['X509SubjectName'] = array(0 => array("_" => $data['x509_subject']));
		$keyinfo[0]['X509Data'][0]['X509IssuerSerial'][0]['X509IssuerName'] = array(0 => array("_" => $data['x509_issuer']));
		$keyinfo[0]['X509Data'][0]['X509IssuerSerial'][0]['X509SerialNumber'] = array(0 => array("_" => intval($data['x509_serial'])));

		$objinfo = array();
		$objinfo[0]['QualifyingProperties'][0]["Target"] = "signature"; 
		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["Id"] = "id-xades-signed-props";
		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["SignedSignatureProperties"][0]['SigningTime'] = array(0 => array("_" => $sign_time));

		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["SignedSignatureProperties"][0]['SigningCertificate'][0]['Cert'][0]['CertDigest'][0]['DigestMethod'] = array(0 => array("_" => "", "Algorithm" => "http://www.w3.org/2001/04/xmlenc#sha256"));
		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["SignedSignatureProperties"][0]['SigningCertificate'][0]['Cert'][0]['CertDigest'][0]['DigestValue'] = array(0 => array("_" => $data['CERTDIGEST']));
		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["SignedSignatureProperties"][0]['SigningCertificate'][0]['Cert'][0]['IssuerSerial'][0]['X509IssuerName'] = array(0 => array("_" => $data['x509_issuer']));
		$objinfo[0]['QualifyingProperties'][0]["SignedProperties"][0]["SignedSignatureProperties"][0]['SigningCertificate'][0]['Cert'][0]['IssuerSerial'][0]['X509SerialNumber'] = array(0 => array("_" => intval($data['x509_serial'])));

		//$json_sign_property = json_encode($sign_property);
		//propsdigest need to follow examples set by others as it is very confusing
		$json_sign_property = stripslashes(json_encode($objinfo[0]['QualifyingProperties']));
		$json_sign_property = substr($json_sign_property, 1, -1);
		$hash = hash('sha256', $json_sign_property, true);
		$PROPDIGEST = base64_encode($hash);
		log_message('error', 'PROPDIGEST CALC:'.$json_sign_property);
		log_message('error', 'PROPDIGEST:'.$PROPDIGEST);

		//signed properties hash
		$digestvalue2 = array(0 => array("_" => $PROPDIGEST));

		$signedinfo = array();
		$signedinfo[0]['SignatureMethod'] = array(0 => array("_" => "", "Algorithm" => "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"));
		$signedinfo[0]['Reference'][0]["Id"] = "id-doc-signed-data";
		$signedinfo[0]['Reference'][0]["Type"] = "";
		$signedinfo[0]['Reference'][0]["URI"] = "";
		$signedinfo[0]['Reference'][0]["DigestMethod"] = $digestmethod;
		$signedinfo[0]['Reference'][0]["DigestValue"] = $digestvalue;
		$signedinfo[0]['Reference'][0]["Id"] = "id-xades-signed-props";
		$signedinfo[0]['Reference'][1]["Type"] = "http://uri.etsi.org/01903/v1.3.2#SignedProperties";
		$signedinfo[0]['Reference'][1]["URI"] = "#id-xades-signed-props";
		$signedinfo[0]['Reference'][1]["DigestMethod"] = $digestmethod2;
		$signedinfo[0]['Reference'][1]["DigestValue"] = $digestvalue2;
		//$signedinfo[0]['Reference'] = array(0 => array("Id" => "id-doc-signed-data", "URI" => "", $digestmethod, $digestvalue));
		//$signedinfo[1]['Reference'] = array(0 => array("Type" => "http://uri.etsi.org/01903/v1.3.2#SignedProperties", "URI" => "#id-xades-signed-props", $digestmethod2, $digestvalue2));

		$ubl = array();
		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionURI'] = array(0 => array("_" => "urn:oasis:names:specification:ubl:dsig:enveloped:xades"));
		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['ID'] = array(0 => array("_" => "urn:oasis:names:specification:ubl:signature:1"));
		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['ReferencedSignatureID'] = array(0 => array("_" => "urn:oasis:names:specification:ubl:signature:Invoice"));
		//$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'] = array(0 => array("Id" => "signature", $signedinfo, $sign_value, $keyinfo, $objinfo));
		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0]["Id"] = "signature";

		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0]["Object"] = $objinfo;

		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0]["KeyInfo"] = $keyinfo;

		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0]["SignatureValue"] = $sign_value;

		$ubl['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0]["SignedInfo"] = $signedinfo;



		$keys = array_keys($json['Invoice'][0]);
		foreach(array_keys($keys) AS $k ){
			$this->array_insert($json['Invoice'][0], $keys[$k], $ubl);
			break;
		}

		foreach(array_keys($keys) AS $k ){
		    $this_value = $json['Invoice'][0][$keys[$k]];
		    $nextval = $json['Invoice'][0][$keys[$k+1]];

			//log_message('error', 'FKEY:'.$keys[$k]);
			//log_message('error', 'NEXT FKEY:'.(isset($keys[$k+1])?$keys[$k+1]:'--END--'));
			//log_message('error', 'NODE:'.print_r($json['Invoice'][0][$keys[$k]], true));

			if ($keys[$k+1] == 'AccountingSupplierParty') {
				$arr = array();
				$arr['Signature'][0]["ID"] = array(0 => array("_" => "urn:oasis:names:specification:ubl:signature:Invoice"));
				$arr['Signature'][0]["SignatureMethod"] = array(0 => array("_" => "urn:oasis:names:specification:ubl:dsig:enveloped:xades"));
				$this->array_insert($json['Invoice'][0], $keys[$k+1], $arr);
				break;
			}

		}

		log_message('error', "RESULT:".json_encode($json));

		//save json in to file
		file_put_contents($data['file_path'].$data['signed_file_name'], json_encode($json));


		return array('status' => 'succ', 'msg' => '');

	}

	/**
	 * @param array      $array
	 * @param int|string $position
	 * @param mixed      $insert
	 */
	function array_insert(&$array, $position, $insert)
	{
	    if (is_int($position)) {
	        array_splice($array, $position, 0, $insert);
	    } else {
	        $pos   = array_search($position, array_keys($array));
	        $array = array_merge(
	            array_slice($array, 0, $pos),
	            $insert,
	            array_slice($array, $pos)
	        );
	    }
	}

}