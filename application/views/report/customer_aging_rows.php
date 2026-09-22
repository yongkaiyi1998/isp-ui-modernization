<div id="table_wrapper">
    <table class="table-striped">
        <thead>
            <tr>
                <th colspan="10">
                    <h3 class="text-center">
                        <?php echo $page_title; ?> <br />
                        <span style="font-size:14px;">AS of <?php echo (empty($as_date) ? date('Y-m-d') : $as_date); ?></span>
                    </h3>
                </th>
            </tr>
            <tr class="report_th" style="background: #289383; font-weight: bold; color: #fff;">
                <th class="col-lg-1" <?php if ($isprint == 1) { echo 'style="width:10%;"'; } ?>>No
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
				if ($order_by == 'customer_no') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="customer_no"></i><?php } ?>
                </th>
                <th class="col-lg-3" <?php if ($isprint == 1) { echo 'style="width:15%;"'; } ?>>Name
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
				if ($order_by == 'customer_name') {
					if ($order_type == 'desc') {
						echo 'fa-sort-desc';
					} else {
						echo 'fa-sort-asc';
					}
				} else {
					echo 'fa-sort';
				}
				?>
                " id="customer_name"></i><?php } ?>
                </th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>0-30 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>31-60 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>61-90 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>91-120 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>121-364 d</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>1-2 y</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>> 2 y</th>
                <th class="text-right col-lg-1" <?php if ($isprint == 1) { echo 'style="width:8%;"'; } ?>>Total A/R
                <?php if ($isprint != 1) { ?><i class="menu-icon fa  
                <?php 
                if ($order_by == 'total_ar') {
                    if ($order_type == 'desc') {
                        echo 'fa-sort-desc';
                    } else {
                        echo 'fa-sort-asc';
                    }
                } else {
                    echo 'fa-sort';
                }
                ?>
                " id="total_ar"></i><?php } ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_ar = 0;
            $total_30 = 0;
            $total_60 = 0;
            $total_90 = 0;
            $total_120 = 0;
            $total_150 = 0;
            $total_365 = 0;
            $total_730 = 0;

            $ctotal_ar = 0;
            $ctotal_30 = 0;
            $ctotal_60 = 0;
            $ctotal_90 = 0;
            $ctotal_120 = 0;
            $ctotal_150 = 0;
            $ctotal_365 = 0;
            $ctotal_730 = 0; 
                                    
            foreach( $data_row AS $row ){
                                            
                /*
                $month4 = 0;
                $month3 = 0;
                $month2 = 0;
                $month1 = 0;
                if( $row['total_payment'] - $row['120DAYS'] > 0 ){
                    $row['total_payment'] = $row['total_payment'] - $row['120DAYS'] ;
                
                    if( $row['total_payment'] - $row['90DAYS'] > 0 ){
                        $row['total_payment'] = $row['total_payment'] - $row['90DAYS'] ;
                        
                        if( $row['total_payment'] - $row['60DAYS'] > 0 ){
                            $row['total_payment'] = $row['total_payment'] - $row['90DAYS'] ;
                    
                            if( $row['total_payment'] - $row['30DAYS'] > 0 ){
                                $row['total_payment'] = $row['total_payment'] - $row['30DAYS'] ;
                            }else{
                                $month1 = $row['30DAYS'] - $row['total_payment'] ;
                            }
                    
                        }else{
                            $month2 = $row['60DAYS'] - $row['total_payment'] ;
                            $month1 = $row['30DAYS'] ;
                        }
                        
                    }else{
                        $month3 = $row['90DAYS'] - $row['total_payment'] ;
                        $month2 = $row['60DAYS'] ;
                        $month1 = $row['30DAYS'] ;
                    }
                
                }else{
                    $month4 = $row['120DAYS'] - $row['total_payment'] ;
                    $month3 = $row['90DAYS'];
                    $month2 = $row['60DAYS'];
                    $month1 = $row['30DAYS'];
                }*/

                    if (($nozero ?? 0) == 1) {
                        if (
                            ($row['month1'] > 0.001) || 
                            ($row['month2'] > 0.001) ||
                            ($row['month3'] > 0.001) ||
                            ($row['month4'] > 0.001) ||
                            ($row['month5'] > 0.001) ||
                            ($row['year1'] > 0.001) ||
                            ($row['year2'] > 0.001) 
                        ) {

                        } else {
                            continue;
                        }
                    }
                
                    echo "<tr>";
                        echo "<td>" . $row['customer_no'] . "</td>";
                        echo "<td>" . $row['customer_name'] . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month1'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month2'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month3'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month4'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['month5'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['year1'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['year2'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['total_ar'],2) . "</td>";
                    echo "</tr>";
            
                    $total_ar += number_format($row['total_ar'],2,'.','');
                    $total_30 += number_format($row['month1'],2,'.','');
                    $total_60 += number_format($row['month2'],2,'.','');
                    $total_90 += number_format($row['month3'],2,'.','');
                    $total_120 += number_format($row['month4'],2,'.','');
                    $total_150 += number_format($row['month5'],2,'.','');
                    $total_365 += number_format($row['year1'],2,'.','');
                    $total_730 += number_format($row['year2'],2,'.','');

                    if ($row['month1'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_30++;
                    }

                    if ($row['month2'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_60++;
                    }

                    if ($row['month3'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_90++;
                    }

                    if ($row['month4'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_120++;
                    }

                    if ($row['month5'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_150++;
                    }

                    if ($row['year1'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_365++;
                    }

                    if ($row['year2'] > 0.001) {
                        $ctotal_ar++;
                        $ctotal_730++;
                    }
            
            }
            
            /*
            foreach ($data_row as $key => $val) {
                echo "<tr><td colspan='100%'><h5>" . $key . "</h5></td></tr>";

                foreach ($val as $key2 => $val2) {
                    echo "<tr>";
                        echo "<td>" . $key2 . "</td>";
                        echo "<td>" . $val2['customer_name'] . "</td>";
                        echo "<td class='text-right'>" . number_format($val2['30'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($val2['60'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($val2['90'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($val2['120'],2) . "</td>";
                        echo "<td class='text-right'>" . number_format($val2['total_ar'],2) . "</td>";
                    echo "</tr>";

                    $total_ar += $val2['total_ar'];
                    $total_30 += $val2['30'];
                    $total_60 += $val2['60'];
                    $total_90 += $val2['90'];
                    $total_120 += $val2['120'];
                }
            }
            */
            echo "<tr>
                <td class='text-right' colspan='2'><strong>Total</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_30, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_60, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_90, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_120, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_150, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_365, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_730, 2, '.', ',') . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . number_format($total_ar, 2, '.', ',') . "</strong></td>
                </tr>";

            //Count
            echo "<tr>
                <td class='text-right' colspan='2'><strong>Total Rows (Not 0$)</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_30 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_60 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_90 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_120 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_150 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_365 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_730 . "</strong></td>
                <td class='text-right top-bottom-bordered'><strong>" . $ctotal_ar . "</strong></td>
                </tr>";
            ?>
        </tbody>
    </table>
</div>