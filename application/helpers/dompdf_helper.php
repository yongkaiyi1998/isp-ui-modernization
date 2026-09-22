<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! function_exists('pdf_create'))
{	
	function pdf_create($html, $filename='', $stream=TRUE) 
	{
		//require_once("dompdf/dompdf_config.inc.php");
		require_once("dompdf3/vendor/autoload.php");

		// instantiate and use the dompdf class
		$options = new Options();
		$CI =& get_instance();
		$options->set('fontDir', $CI->config->item('dompdf_font_dir'));
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);

		//$dompdf = new DOMPDF();
		//~ $dompdf->set_paper(DEFAULT_PDF_PAPER_SIZE, 'portrait');
		//~ $dompdf->set_paper('A4', 'portrait');
		//~ $paper_size = array(0,0,360,360);
		//~ $dompdf->set_paper($paper_size);
		
		$dompdf->load_html($html);
		$dompdf->render();
		if ($stream) {
			$dompdf->stream($filename.".pdf");
		} else {
			return $dompdf->output();
		}
	}

}
