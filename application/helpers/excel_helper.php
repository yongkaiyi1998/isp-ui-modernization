<?php

if ( ! function_exists('prepare_excel'))
{
	function prepare_excel() {
		require_once APPPATH.'helpers/phpexcel/PHPExcel.php';

		$objPHPExcel = new PHPExcel();

		return $objPHPExcel;

	}

}

if (! function_exists('prepare_writer'))
{
	function prepare_writer($objPHPExcel) {
		require_once APPPATH.'helpers/phpexcel/PHPExcel.php';
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		return $objWriter;
	}
}