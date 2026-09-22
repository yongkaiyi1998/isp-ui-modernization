<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?php echo base_url("css/theme/bootstrap.css?".cssjs_ver()); ?> "/>
    <title>Form Validation Rule Reference</title>
</head>
<style>
    body {
        padding-top: 20px;
        background: #f8f8f8;
    }
    .container {
        background: #fff;
        padding: 30px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 {
        margin-bottom: 20px;
        color: #337ab7;
    }
    .note {
        background: #fcf8e3;
        border-left: 5px solid #faebcc;
        padding: 15px;
        border-radius: 4px;
        color: #8a6d3b;
        margin-top: 20px;
    }
    code {
        background: #f5f5f5;
        padding: 2px 5px;
        border-radius: 4px;
        font-size: 90%;
        color: #d9534f;
    }
</style>
</head>
<body>

<div class="container">
    <h2 class="text-center text-warning">Form Validation Rule Reference</h2>
    <p class="text-center">List of all native rules available in CodeIgniter's Form Validation library:</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="bg-warning">
                <tr>
                    <th>Rule</th>
                    <th>Parameter</th>
                    <th>Description</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>required</td><td>No</td><td>Returns FALSE if the form element is empty.</td><td></td></tr>
                <tr><td>matches</td><td>Yes</td><td>Returns FALSE if the form element does not match the one in the parameter.</td><td><code>matches[form_item]</code></td></tr>
                <tr><td>regex_match</td><td>Yes</td><td>Returns FALSE if the form element does not match the regular expression.</td><td><code>regex_match[/regex/]</code></td></tr>
                <tr><td>differs</td><td>Yes</td><td>Returns FALSE if the form element does not differ from the one in the parameter.</td><td><code>differs[form_item]</code></td></tr>
                <tr><td>is_unique</td><td>Yes</td><td>Returns FALSE if the element is not unique in the specified table and field. Requires Query Builder enabled.</td><td><code>is_unique[table.field]</code></td></tr>
                <tr><td>min_length</td><td>Yes</td><td>Returns FALSE if the form element is shorter than the parameter value.</td><td><code>min_length[3]</code></td></tr>
                <tr><td>max_length</td><td>Yes</td><td>Returns FALSE if the form element is longer than the parameter value.</td><td><code>max_length[12]</code></td></tr>
                <tr><td>exact_length</td><td>Yes</td><td>Returns FALSE if the form element is not exactly the parameter value.</td><td><code>exact_length[8]</code></td></tr>
                <tr><td>greater_than</td><td>Yes</td><td>Returns FALSE if the form element is less than or equal to the parameter value or not numeric.</td><td><code>greater_than[8]</code></td></tr>
                <tr><td>greater_than_equal_to</td><td>Yes</td><td>Returns FALSE if the form element is less than the parameter value or not numeric.</td><td><code>greater_than_equal_to[8]</code></td></tr>
                <tr><td>less_than</td><td>Yes</td><td>Returns FALSE if the form element is greater than or equal to the parameter value or not numeric.</td><td><code>less_than[8]</code></td></tr>
                <tr><td>less_than_equal_to</td><td>Yes</td><td>Returns FALSE if the form element is greater than the parameter value or not numeric.</td><td><code>less_than_equal_to[8]</code></td></tr>
                <tr><td>in_list</td><td>Yes</td><td>Returns FALSE if the form element is not within a predetermined list.</td><td><code>in_list[red,blue,green]</code></td></tr>
                <tr><td>alpha</td><td>No</td><td>Returns FALSE if the form element contains anything other than alphabetical characters.</td><td></td></tr>
                <tr><td>alpha_numeric</td><td>No</td><td>Returns FALSE if the form element contains anything other than alpha-numeric characters.</td><td></td></tr>
                <tr><td>alpha_numeric_spaces</td><td>No</td><td>Returns FALSE if the form element contains anything other than alpha-numeric characters or spaces.</td><td></td></tr>
                <tr><td>alpha_dash</td><td>No</td><td>Returns FALSE if the form element contains anything other than alpha-numeric characters, underscores or dashes.</td><td></td></tr>
                <tr><td>numeric</td><td>No</td><td>Returns FALSE if the form element contains anything other than numeric characters.</td><td></td></tr>
                <tr><td>integer</td><td>No</td><td>Returns FALSE if the form element contains anything other than an integer.</td><td></td></tr>
                <tr><td>decimal</td><td>No</td><td>Returns FALSE if the form element contains anything other than a decimal number.</td><td></td></tr>
                <tr><td>is_natural</td><td>No</td><td>Returns FALSE if the form element contains anything other than a natural number: 0,1,2,3...</td><td></td></tr>
                <tr><td>is_natural_no_zero</td><td>No</td><td>Returns FALSE if the form element contains anything other than a natural number greater than zero: 1,2,3...</td><td></td></tr>
                <tr><td>valid_url</td><td>No</td><td>Returns FALSE if the form element does not contain a valid URL.</td><td></td></tr>
                <tr><td>valid_email</td><td>No</td><td>Returns FALSE if the form element does not contain a valid email address.</td><td></td></tr>
                <tr><td>valid_emails</td><td>No</td><td>Returns FALSE if any value in a comma-separated list is not a valid email.</td><td></td></tr>
                <tr><td>valid_ip</td><td>Yes</td><td>Returns FALSE if the supplied IP address is not valid. Optional: 'ipv4' or 'ipv6'.</td><td></td></tr>
                <tr><td>valid_base64</td><td>No</td><td>Returns FALSE if the supplied string contains anything other than valid Base64 characters.</td><td></td></tr>
            </tbody>
        </table>
    </div>

    <div class="note">
        <p>Note: You can also call rules as methods, e.g., <code>$this->form_validation->required($string);</code></p>
        <p>Native PHP functions with up to two parameters can also be used, where at least one parameter passes the field data.</p>
    </div>

</div>
</body>
</html>
