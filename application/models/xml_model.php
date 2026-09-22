<?php
class Xml_model extends MY_Model{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('zip');
		$this->load->model('common_model');
		
	}

	public function xml_clear_folder( $type ){
		
		$file_path = FCPATH . "files/xml_" . $type . "/" ;
		
		if(is_dir($file_path))
		{
			$files = scandir($file_path);
			foreach($files as $file)
			{
				if( $file != "." && $file != ".." )
				{
					unlink($file_path."/".$file);
				}
			}
		}
		
		/*
		$folders = scandir( $file_path );
		foreach($folders as $folder){
			if( $folder != "." && $folder != ".." ){
				if( strstr( $folder , $type ) && is_dir( $file_path.$folder ) ){
					$files = scandir( $file_path . $folder . "/" );
					foreach( $files as $file ){
						if( $file != "." && $file != ".." )
							unlink($file_path.$folder."/".$file);
					}
					rmdir( $file_path . $folder );
				}elseif( strstr( $folder , $type ) && !is_dir( $file_path.$folder ) ){
					unlink($file_path.$folder);
				}
			}
		}
		*/
	}

	public function xml_invoice($qwhere){
		$this->xml_clear_folder('INV');
		$this->load->model('bill_model');
		$this->load->model('customer_model');
		$bills = $this->bill_model->get_export_filtered( $qwhere ) ;	
		
		//~ unset( $bills );
		//~ $bills['bill_no'][] = '201887';	
		if( !empty($bills) ){
			foreach( $bills as $bill ){
				
				$bill_no = $bill['bill_no'];
				$bill_info = $this->bill_model->get_invoice( $bill_no ) ;

				if(trim($bill_info[$bill_no]['bill_addr1']) != '')
					$addr1 = trim($bill_info[$bill_no]['bill_addr1']) ;
				elseif( trim($bill_info[$bill_no]['inst_addr1']) != '')
					$addr1 = trim($bill_info[$bill_no]['inst_addr1']) ;
				else
					$addr1 = '';
				
				if( trim($bill_info[$bill_no]['bill_addr2']) != '' )
					$addr2 = trim($bill_info[$bill_no]['bill_addr2']) ;
				elseif( trim($bill_info[$bill_no]['inst_addr2']) != '' )
					$addr2 = trim($bill_info[$bill_no]['inst_addr2']) ;
				else
					$addr2 = '';
				
				if( $bill_info[$bill_no]['bill_postcode'] != '' && $bill_info[$bill_no]['bill_city'] != '' )
					$addr3 = trim($bill_info[$bill_no]['bill_postcode'] . ' ' . $bill_info[$bill_no]['bill_city']) ;
				elseif( $bill_info[$bill_no]['inst_postcode'] != '' && $bill_info[$bill_no]['inst_city'] != '' )
					$addr3 = trim($bill_info[$bill_no]['inst_postcode'] . ' ' . $bill_info[$bill_no]['inst_city']) ;
				else
					$addr3 = '';
				
				if( $bill_info[$bill_no]['is_void'] == 1 )
					$cancel = 'T';
				else
					$cancel = 'F';
				
				//bills = DR at header, randomly pick 1 from sys_ledger_account ?	
				$ledger_info = $this->customer_model->get_customer_category( $bill_info[$bill_no]['customer_category'] );
				//$ledger_info['ledger_account_code']
				$xml_invoice_info = '
				  <ROWDATA>
					<ROW DOCKEY="'.$bill_info[$bill_no]['idx'].'" DOCNO="'.htmlentities($bill_info[$bill_no]['bill_no']).'" 
						DOCDATE="'.htmlentities($bill_info[$bill_no]['bill_date']).'"
						POSTDATE="'.htmlentities($bill_info[$bill_no]['bill_date']).'" 
						TAXDATE="'.htmlentities($bill_info[$bill_no]['bill_date']).'" 
						CODE="'.htmlentities($bill_info[$bill_no]['customer_no']).'" 
						COMPANYNAME="'.htmlentities($bill_info[$bill_no]['customer_name']).'" 
						ADDRESS1="'.htmlentities($addr1).'" 
						ADDRESS2="'.htmlentities($addr2).'" ADDRESS3="'.htmlentities($addr3).'" 
						ADDRESS4="'.htmlentities($bill_info[$bill_no]['bill_state']!=''?$bill_info[$bill_no]['bill_state']:$bill_info[$bill_no]['inst_state']).'" 
						PHONE1="'.htmlentities($bill_info[$bill_no]['tel_num']!=''?$bill_info[$bill_no]['tel_num']:'').'" 
						FAX1="'.htmlentities($bill_info[$bill_no]['fax_num']!=''?$bill_info[$bill_no]['fax_num']:'').'" 
						ATTENTION="'.htmlentities( $bill_info[$bill_no]['pic_name']!=''?$bill_info[$bill_no]['pic_name']:$bill_info[$bill_no]['customer_name'] ).'"
						AREA="----" AGENT="----" PROJECT="----" TERMS="'.htmlentities($bill_info[$bill_no]['payment_term']).' days" 
						CURRENCYCODE="----" CURRENCYRATE="1.0000000000" 
						SHIPPER="----" DESCRIPTION="Sales" CANCELLED="'.$cancel.'" 
						DOCAMT="'.htmlentities($bill_info[$bill_no]['amount']).'" 
						LOCALDOCAMT="'.htmlentities($bill_info[$bill_no]['amount']).'" 
						D_AMOUNT="0.00" BRANCHNAME="" DADDRESS1="" TRANSFERABLE="T" PRINTCOUNT="0" DOCNOSETKEY="0" 
						CHANGED="F">';

				 //loops
				 //<sdsDocDetail></sdsDocDetail>
				$dtl_seq = 1 ;
				$xml_invoice_details = '';
				$bill_details = $this->bill_model->get_bill_itemized($bill_no);
				//foreach( $bill_info[$bill_no]['bill_detail'] AS $bill_detail ){
				foreach( $bill_details AS $bill_detail ){
				$dtl_gl = $this->common_model->get_ledger_account_codes( 'BILL', $bill_detail['bill_type_id'], 
																		 $bill_info[$bill_no]['customer_category'], 'CR' ) ;
					
					$xml_invoice_detail = ' 
						<ROWsdsDocDetail DTLKEY="'.$bill_detail['idx'].'" DOCKEY="'.$bill_no.'" SEQ="'.$dtl_seq.'" 
							ITEMCODE="'.htmlentities($bill_detail['bill_type_name']).'" LOCATION="----" BATCH="" PROJECT="----" 
							DESCRIPTION="'.htmlentities($bill_detail['remark']).'" DESCRIPTION2="" DESCRIPTION3="" 
							QTY="1.0000" UOM="UNIT" RATE="1.0000" 
							SQTY="1.0000" SUOMQTY="1.0000" UNITPRICE="'.$bill_detail['amount'].'" 
							DELIVERYDATE="'.date( 'Ymd' , strtotime($bill_detail['tranx_date']) ).'" 
							DISC="" TAX="'.htmlentities($bill_detail['tax_code']).'" 
							TAXAMT="'.number_format( $bill_detail['tax_amount'] , 2 , '.' ,'' ).'" 
							LOCALTAXAMT="'.number_format( $bill_detail['tax_amount'] , 2 , '.' ,'' ).'" 
							TAXINCLUSIVE="0" AMOUNT="'.$bill_detail['amount'].'" 
							LOCALAMOUNT="'.$bill_detail['amount'].'" 
							ACCOUNT="'.htmlentities($dtl_gl[0]['ledger_account_code']).'" 
							PRINTABLE="T" TRANSFERABLE="T" TAXABLEAMT="'.$bill_detail['amount'].'" 
							CHANGED="F">
						  <sdsSerialNumber/>
						</ROWsdsDocDetail>';

					
					$xml_invoice_details .= $xml_invoice_detail ;
					$dtl_seq++ ;
				}
				$xml_invoices['content'][] = $xml_invoice_info . '<sdsDocDetail>' . $xml_invoice_details . '</sdsDocDetail></ROW></ROWDATA>';
				$xml_invoices['bill_no'][] = $bill_no;
				$xml_invoices['customer_no'][] = $bill_info[$bill_no]['customer_no'];
			}
			
			if( sizeof( $xml_invoices['content'] ) > 0 ){
				$file_path = FCPATH . "files/xml_INV/" ;
				if( is_dir($file_path) ){
					//mkdir($file_path,0777,true);				
					for( $z = 0 ; $z < sizeof($xml_invoices['content']) ; $z++ ){
						$str_xml = $this->_invoice_header . $xml_invoices['content'][$z] . $this->_invoice_footer ;
						//echo '# ] '.$z . ' --> ' . $xml_invoices['bill_no'][$z] .'<br />';
						$dom = new DOMDocument();
						//to prevent WARNING			
						libxml_use_internal_errors(true);
						$dom->loadXML($this->_invoice_header . $xml_invoices['content'][$z] . $this->_invoice_footer);
						libxml_use_internal_errors(false);
						$dom->formatOutput = true;
						$xmlString = $dom->saveXML();
						$new_file_name = $file_path.'SL_IV.IV-'.$xml_invoices['bill_no'][$z].'.'.$xml_invoices['customer_no'][$z].'.xml';
						$dom->save( $new_file_name );
						$this->zip->read_file($new_file_name,FALSE);
					}

					//$this->zip->read_dir($file_path,FALSE);
					$this->zip->archive( $file_path . "INV_" . date("Ymd") .'.zip');
					// DO NOT ECHO ANYTHING b4 zip->download
					ob_end_clean();
					$this->zip->download("INV_" . date("Ymd"));
					
				}
			}
		}else{
			$this->session->set_flashdata("warning_msg", 'Bills not found! You have failed to export bills.');
			redirect('bill/export_bill');
		}
	}

	public function xml_payment($qwhere){
		
		$this->load->model('payment_model');
		$this->load->model('customer_model');
		$payments = $this->payment_model->get_payment_filtered( $qwhere ) ;	
		
		if(!empty($payments)){
			
			foreach( $payments AS $payment ){
				
				//$ledger_info = $this->common_model->get_ledger_account_code('PAYMENT', $payment['payment_source'], $payment['customer_category']);
				
				$ledger_info = $this->customer_model->get_customer_category( $payment['customer_category'] );
				$payment_gl = $this->common_model->get_ledger_account_codes( 'PAYMENT', $payment['payment_source'], $payment['customer_category'], 'DR' ) ;

				$xml_payment_info = '
				  <ROWDATA>
					<ROW DOCKEY="'.htmlentities($payment['idx']).'" DOCNO="'.htmlentities($payment['payment_no']).'" 
					CODE="'.htmlentities($payment['customer_no']).'" 
					DOCDATE="'.htmlentities($payment['pay_date']).'" POSTDATE="'.htmlentities($payment['pay_date']).'" 
					DESCRIPTION="'.htmlentities($payment['remark']).'" 
					AREA="----" AGENT="----" 
					PAYMENTMETHOD="'.htmlentities($payment_gl[0]['ledger_account_code']).'" 
					JOURNAL="CASH" 
					PROJECT="----"
					PAYMENTPROJECT="----" CURRENCYCODE="----" CURRENCYRATE="1.0000000000" 
					BANKCHARGE="0.00" DOCAMT="'.htmlentities($payment['amount']).'" 
					LOCALDOCAMT="'.htmlentities($payment['amount']).'" 
					UNAPPLIEDAMT="'.htmlentities($payment['amount']).'" CANCELLED="F" DOCNOSETKEY="0" CHANGED="F">
					  <sdsKnockOff>
					  </sdsKnockOff>
					</ROW>
				  </ROWDATA>';
				  //PAYMENTMETHOD="325-000" JOURNAL="CASH"
				  //<sdsKnockOff>
				  //<ROWsdsKnockOff DOCKEY="-1" KNOCKOFFDOCKEY="-1" REFDOCKEY="-1" DOCTYPE="IV" UNIQUEKEY="-1" DOCNO="POS1" KOAMT="1000.00" KNOCKOFF="T"/>

				$xml_payments['content'][] = $xml_payment_info ;
				$xml_payments['payment_no'][] = $payment['payment_no'];
				$xml_payments['customer_no'][] = $payment['customer_no'];
				//echo htmlspecialchars($xml_payment_info) . '<br /><br />';
			}
			
			if( sizeof( $xml_payments['content'] ) > 0 ){
				
				$this->xml_clear_folder( 'PM' );
				$file_path = FCPATH . "files/xml_PM/" ;
				//$file_name = "PM_" . date('Ymd') ;
				if( is_dir($file_path) ){
				
					//$file_path_name = $file_path . $file_name . "/";
					
					for( $z = 0 ; $z < sizeof($xml_payments['content']) ; $z++ ){
						$dom = new DOMDocument();
						//to prevent WARNING
						//libxml_use_internal_errors(true);
						$dom->loadXML($this->_payment_header . $xml_payments['content'][$z] . $this->_payment_footer);
						//libxml_use_internal_errors(false);
						$dom->formatOutput = true;
						$xmlString = $dom->saveXML();
						
						$new_file_name = $file_path.'AR_PM.PM-'.$xml_payments['payment_no'][$z].'.'.$xml_payments['customer_no'][$z].'.xml' ; 
						$dom->save( $new_file_name );
						$this->zip->read_file($new_file_name,FALSE);
					}
					
					$this->zip->archive( $file_path . 'PM_' . date('Ymd') . '.zip');
					// DO NOT ECHO ANYTHING b4 zip->download
					ob_end_clean(); 
					$this->zip->download('PM_'.date('Ymd'));
				}
			}
		}else{
			$this->session->set_flashdata("warning_msg", 'Payments not found! You have failed to export payments.');
			redirect('payment/export_payment');
		}
	}

	protected $_invoice_header = '<?xml version="1.0" standalone="yes"?>
<DATAPACKET Version="2.0">
  <METADATA>
	<FIELDS>
	  <FIELD attrname="DOCKEY" fieldtype="i4" required="true"/>
	  <FIELD attrname="DOCNO" fieldtype="string" required="true" WIDTH="20"/>
	  <FIELD attrname="DOCNOEX" fieldtype="string" WIDTH="20"/>
	  <FIELD attrname="DOCDATE" fieldtype="date"/>
	  <FIELD attrname="POSTDATE" fieldtype="date"/>
	  <FIELD attrname="TAXDATE" fieldtype="date"/>
	  <FIELD attrname="CODE" fieldtype="string" WIDTH="10"/>
	  <FIELD attrname="COMPANYNAME" fieldtype="string" WIDTH="100"/>
	  <FIELD attrname="ADDRESS1" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="ADDRESS2" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="ADDRESS3" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="ADDRESS4" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="PHONE1" fieldtype="string" WIDTH="30"/>
	  <FIELD attrname="FAX1" fieldtype="string" WIDTH="30"/>
	  <FIELD attrname="ATTENTION" fieldtype="string" WIDTH="70"/>
	  <FIELD attrname="AREA" fieldtype="string" WIDTH="10"/>
	  <FIELD attrname="AGENT" fieldtype="string" WIDTH="10"/>
	  <FIELD attrname="PROJECT" fieldtype="string" WIDTH="20"/>
	  <FIELD attrname="TERMS" fieldtype="string" WIDTH="10"/>
	  <FIELD attrname="CURRENCYCODE" fieldtype="string" WIDTH="6"/>
	  <FIELD attrname="CURRENCYRATE" fieldtype="fixedFMT" DECIMALS="10" WIDTH="19"/>
	  <FIELD attrname="SHIPPER" fieldtype="string" required="true" WIDTH="30"/>
	  <FIELD attrname="DESCRIPTION" fieldtype="string" WIDTH="150"/>
	  <FIELD attrname="CANCELLED" fieldtype="string" WIDTH="1"/>
	  <FIELD attrname="DOCAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
	  <FIELD attrname="LOCALDOCAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
	  <FIELD attrname="D_AMOUNT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
	  <FIELD attrname="VALIDITY" fieldtype="string" WIDTH="150"/>
	  <FIELD attrname="DELIVERYTERM" fieldtype="string" WIDTH="150"/>
	  <FIELD attrname="CC" fieldtype="string" WIDTH="150"/>
	  <FIELD attrname="DOCREF1" fieldtype="string" WIDTH="25"/>
	  <FIELD attrname="DOCREF2" fieldtype="string" WIDTH="25"/>
	  <FIELD attrname="DOCREF3" fieldtype="string" WIDTH="25"/>
	  <FIELD attrname="DOCREF4" fieldtype="string" WIDTH="25"/>
	  <FIELD attrname="BRANCHNAME" fieldtype="string" WIDTH="100"/>
	  <FIELD attrname="DADDRESS1" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="DADDRESS2" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="DADDRESS3" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="DADDRESS4" fieldtype="string" WIDTH="40"/>
	  <FIELD attrname="DATTENTION" fieldtype="string" WIDTH="70"/>
	  <FIELD attrname="DPHONE1" fieldtype="string" WIDTH="30"/>
	  <FIELD attrname="DFAX1" fieldtype="string" WIDTH="30"/>
	  <FIELD attrname="ATTACHMENTS" fieldtype="bin.hex" SUBTYPE="Binary" WIDTH="8"/>
	  <FIELD attrname="NOTE" fieldtype="bin.hex" SUBTYPE="Binary" WIDTH="8"/>
	  <FIELD attrname="TRANSFERABLE" fieldtype="string" WIDTH="1"/>
	  <FIELD attrname="UPDATECOUNT" fieldtype="i4"/>
	  <FIELD attrname="PRINTCOUNT" fieldtype="i4"/>
	  <FIELD attrname="DOCNOSETKEY" fieldtype="i8" required="true"/>
	  <FIELD attrname="NEXTDOCNO" fieldtype="string" WIDTH="20"/>
	  <FIELD attrname="CHANGED" fieldtype="string" required="true" WIDTH="1"/>
	  <FIELD attrname="sdsDocDetail" fieldtype="nested">
		<FIELDS>
		  <FIELD attrname="DTLKEY" fieldtype="i4" required="true"/>
		  <FIELD attrname="DOCKEY" fieldtype="i4" required="true"/>
		  <FIELD attrname="SEQ" fieldtype="i4"/>
		  <FIELD attrname="STYLEID" fieldtype="string" WIDTH="5"/>
		  <FIELD attrname="NUMBER" fieldtype="string" WIDTH="5"/>
		  <FIELD attrname="ITEMCODE" fieldtype="string" WIDTH="30"/>
		  <FIELD attrname="LOCATION" fieldtype="string" WIDTH="20"/>
		  <FIELD attrname="BATCH" fieldtype="string" WIDTH="30"/>
		  <FIELD attrname="PROJECT" fieldtype="string" WIDTH="20"/>
		  <FIELD attrname="DESCRIPTION" fieldtype="string" WIDTH="200"/>
		  <FIELD attrname="DESCRIPTION2" fieldtype="string" WIDTH="200"/>
		  <FIELD attrname="DESCRIPTION3" fieldtype="bin.hex" SUBTYPE="Binary" WIDTH="8"/>
		  <FIELD attrname="QTY" fieldtype="fixedFMT" DECIMALS="4" WIDTH="19"/>
		  <FIELD attrname="UOM" fieldtype="string" WIDTH="10"/>
		  <FIELD attrname="RATE" fieldtype="fixedFMT" DECIMALS="4" WIDTH="19"/>
		  <FIELD attrname="SQTY" fieldtype="fixedFMT" DECIMALS="4" WIDTH="19"/>
		  <FIELD attrname="SUOMQTY" fieldtype="fixedFMT" DECIMALS="4" WIDTH="19"/>
		  <FIELD attrname="UNITPRICE" fieldtype="fixedFMT" DECIMALS="8" WIDTH="19"/>
		  <FIELD attrname="DELIVERYDATE" fieldtype="date"/>
		  <FIELD attrname="DISC" fieldtype="string" WIDTH="20"/>
		  <FIELD attrname="TAX" fieldtype="string" WIDTH="10"/>
		  <FIELD attrname="TAXAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
		  <FIELD attrname="LOCALTAXAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
		  <FIELD attrname="TAXINCLUSIVE" fieldtype="i2"/>
		  <FIELD attrname="AMOUNT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
		  <FIELD attrname="LOCALAMOUNT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
		  <FIELD attrname="ACCOUNT" fieldtype="string" WIDTH="10"/>
		  <FIELD attrname="PRINTABLE" fieldtype="string" WIDTH="1"/>
		  <FIELD attrname="FROMDOCTYPE" fieldtype="string" WIDTH="2"/>
		  <FIELD attrname="FROMDOCKEY" fieldtype="i4"/>
		  <FIELD attrname="FROMDTLKEY" fieldtype="i4"/>
		  <FIELD attrname="TRANSFERABLE" fieldtype="string" WIDTH="1"/>
		  <FIELD attrname="REMARK1" fieldtype="string" WIDTH="200"/>
		  <FIELD attrname="REMARK2" fieldtype="string" WIDTH="200"/>
		  <FIELD attrname="CHANGED" fieldtype="string" required="true" WIDTH="1"/>
		  <FIELD attrname="sdsSerialNumber" fieldtype="nested">
			<FIELDS>
			  <FIELD attrname="SERIALNUMBER" fieldtype="string" required="true" WIDTH="30"/>
			</FIELDS>
			<PARAMS/>
		  </FIELD>
		</FIELDS>
		<PARAMS/>
	  </FIELD>
	</FIELDS>
	<PARAMS/>
  </METADATA>';
	protected $_invoice_footer = '</DATAPACKET>';
	

	protected $_payment_header = '<?xml version="1.0" standalone="yes"?>
<DATAPACKET Version="2.0">
  <METADATA>
    <FIELDS>
      <FIELD attrname="DOCKEY" fieldtype="i4" required="true"/>
      <FIELD attrname="DOCNO" fieldtype="string" required="true" WIDTH="20"/>
      <FIELD attrname="CODE" fieldtype="string" WIDTH="10"/>
      <FIELD attrname="DOCDATE" fieldtype="date"/>
      <FIELD attrname="POSTDATE" fieldtype="date"/>
      <FIELD attrname="DESCRIPTION" fieldtype="string" WIDTH="150"/>
      <FIELD attrname="AREA" fieldtype="string" WIDTH="10"/>
      <FIELD attrname="AGENT" fieldtype="string" WIDTH="10"/>
      <FIELD attrname="PAYMENTMETHOD" fieldtype="string" WIDTH="10"/>
      <FIELD attrname="CHEQUENUMBER" fieldtype="string" WIDTH="20"/>
      <FIELD attrname="JOURNAL" fieldtype="string" WIDTH="10"/>
      <FIELD attrname="PROJECT" fieldtype="string" WIDTH="20"/>
      <FIELD attrname="PAYMENTPROJECT" fieldtype="string" WIDTH="20"/>
      <FIELD attrname="CURRENCYCODE" fieldtype="string" WIDTH="6"/>
      <FIELD attrname="CURRENCYRATE" fieldtype="fixedFMT" DECIMALS="10" WIDTH="19"/>
      <FIELD attrname="BANKCHARGE" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
      <FIELD attrname="DOCAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
      <FIELD attrname="LOCALDOCAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
      <FIELD attrname="UNAPPLIEDAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
      <FIELD attrname="FROMDOCTYPE" fieldtype="string" WIDTH="2"/>
      <FIELD attrname="CANCELLED" fieldtype="string" WIDTH="1"/>
      <FIELD attrname="BOUNCEDDATE" fieldtype="date"/>
      <FIELD attrname="UPDATECOUNT" fieldtype="i4"/>
      <FIELD attrname="ATTACHMENTS" fieldtype="bin.hex" SUBTYPE="Binary" WIDTH="8"/>
      <FIELD attrname="NOTE" fieldtype="bin.hex" SUBTYPE="Binary" WIDTH="8"/>
      <FIELD attrname="DOCNOSETKEY" fieldtype="i8" required="true"/>
      <FIELD attrname="NEXTDOCNO" fieldtype="string" WIDTH="20"/>
      <FIELD attrname="CHANGED" fieldtype="string" required="true" WIDTH="1"/>
      <FIELD attrname="sdsKnockOff" fieldtype="nested">
        <FIELDS>
          <FIELD attrname="DOCKEY" fieldtype="i4" required="true"/>
          <FIELD attrname="UPDATECOUNT" fieldtype="i4"/>
          <FIELD attrname="KNOCKOFFDOCKEY" fieldtype="i4" required="true"/>
          <FIELD attrname="REFDOCKEY" fieldtype="i4" required="true"/>
          <FIELD attrname="DOCTYPE" fieldtype="string" required="true" WIDTH="2"/>
          <FIELD attrname="UNIQUEKEY" fieldtype="string" required="true" WIDTH="12"/>
          <FIELD attrname="DOCDATE" fieldtype="date"/>
          <FIELD attrname="POSTDATE" fieldtype="date"/>
          <FIELD attrname="DOCNO" fieldtype="string" required="true" WIDTH="20"/>
          <FIELD attrname="DOCAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="CURRENCYCODE" fieldtype="string" WIDTH="6"/>
          <FIELD attrname="CURRENCYRATE" fieldtype="fixedFMT" DECIMALS="10" WIDTH="19"/>
          <FIELD attrname="ORIGINALOUTSTANDING" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="OUTSTANDING" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="KOAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="ACTUALLOCALKOAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="LOCALKOAMT" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="GAINLOSS" fieldtype="fixedFMT" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="GAINLOSSPOSTDATE" fieldtype="date"/>
          <FIELD attrname="LOCALOUTSTANDING" fieldtype="fixedFMT" required="true" DECIMALS="2" WIDTH="19"/>
          <FIELD attrname="KNOCKOFF" fieldtype="string" required="true" WIDTH="1"/>
          <FIELD attrname="DOCNOEX" fieldtype="string" WIDTH="20"/>
          <FIELD attrname="DESCRIPTION" fieldtype="string" WIDTH="150"/>
          <FIELD attrname="DUEDATE" fieldtype="date"/>
          <FIELD attrname="PROJECT" fieldtype="string" WIDTH="20"/>
        </FIELDS>
        <PARAMS/>
      </FIELD>
    </FIELDS>
    <PARAMS/>
  </METADATA>';
	
	protected $_payment_footer = '</DATAPACKET>';
}

