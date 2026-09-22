<div class="modal fade" id="tinTutorialModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header" style="background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="font-weight: 600; font-size: 20px;">How to Get Customer TIN</h4>
      </div>

      <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 25px;">

        <p style="font-size: 14px; line-height: 1.6;">
          This guide shows how an agent can help their customer retrieve their TIN. Operators/agents may also use this guide to assist customers using their IC or passport.
        </p>

        <hr style="margin: 20px 0; border-color: #ddd;">

        <h5 style="font-weight: 600; margin-bottom: 15px;">Step-by-Step Guide</h5>

        <!-- Step 1 -->
        <div class="panel panel-default" style="border-radius: 8px; border-color: #ddd; margin-bottom: 20px;">
          <div class="panel-heading" style="background-color: #f9f9f9; font-weight: bold;">Step 1: Go to LHDN MyTax</div>
          <div class="panel-body">
            <p>Visit the <a href="https://mytax.hasil.gov.my/" target="_blank">LHDN MyTax</a> website.</p>
            <div class="text-center">
              <img src="<?= base_url('images/mytax-tutorial1.png'); ?>" alt="mytax-login-preview" class="img-responsive" style="max-width: 70%; border: 1px solid #ddd; border-radius: 8px; padding: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="panel panel-default" style="border-radius: 8px; border-color: #ddd; margin-bottom: 20px;">
          <div class="panel-heading" style="background-color: #f9f9f9; font-weight: bold;">Step 2: Search for TIN</div>
          <div class="panel-body">
            <p>After logging in, click <q>Carian TIN</q> to search for a TIN using the customer's IC.</p>
            <div class="text-center">
              <img src="<?= base_url('images/mytax-tutorial3.png'); ?>" alt="mytax-dashboard-preview" class="img-responsive" style="max-width: 70%; border: 1px solid #ddd; border-radius: 8px; padding: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="panel panel-default" style="border-radius: 8px; border-color: #ddd; margin-bottom: 20px;">
          <div class="panel-heading" style="background-color: #f9f9f9; font-weight: bold;">Step 3: View Result</div>
          <div class="panel-body">
            <p>The system will display the TIN for the given IC.</p>
            <div class="text-center">
              <img src="<?= base_url('images/mytax-tutorial4.png'); ?>" alt="mytax-dashboard-preview" class="img-responsive" style="max-width: 70%; border: 1px solid #ddd; border-radius: 8px; padding: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
            </div>
          </div>
        </div>

        <hr style="margin: 20px 0; border-color: #ddd;">

        <!-- General TIN Section -->
        <div class="panel panel-info" style="border-radius: 8px; border-color: #17a2b8;">
          <div class="panel-heading" style="background-color: #e9f7fb; font-weight: bold;">If Customer Does Not Provide TIN</div>
          <div class="panel-body">
            <p>Use a Designated <strong>GENERAL TIN</strong> for e-invoicing:</p>
            <ul style="font-size: 14px; line-height: 1.6;">
              <li><strong style="color: #007bff;">EI00000000010</strong> - General Public</li>
              <li><strong style="color: #007bff;">EI00000000020</strong> - Foreign Buyer</li>
              <li><strong style="color: #007bff;">EI00000000030</strong> - Foreign Supplier</li>
              <li><strong style="color: #007bff;">EI00000000040</strong> - Government</li>
            </ul>
            <p style="font-size: 12px; color: #555;"><i>Note: If the customer has never registered, a new TIN must be created through the LHDN system.</i></p>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
