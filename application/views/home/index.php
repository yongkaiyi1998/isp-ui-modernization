<div class="container home">
  <!-- Page Header -->
  <div class="page-header border-0 pb-3">
    <h1 class="text-dark-m3 pb-0 mb-1 text-200">Overview &amp; Stats (Last Month: <?= $last_month_start; ?> to <?= $last_month_end; ?>)</h1>
    <div class="page-tools">
      <div class="action-buttons text-nowrap">
        <span class="red">*Next refresh: <?= $next_month_refresh; ?></span>
        <a class="btn bgc-white btn-light-secondary mx-0 px-3 py-2" href="#" data-toggle="tooltip" title="Refresh" onclick="location.reload()">
          <i class="fa fa-refresh text-primary"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Dashboard Cards -->
  <div class="row px-2 align-items-stretch">

    <?php if ($show_this_month_payment): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="mb-1">
            <span class="d-inline-block bgc-success-l2 p-4 radius-round">
              <i class="fa fa-dollar text-success-m1 text-180 w-4"></i>
            </span>
          </div>
          <div class="mt-1">
            <div class="text-secondary-d3 text-200 mt-4">RM <?= number_format($this_month_payments['total_amount'], 2); ?></div>
            <div class="text-dark-tp4 text-110 mt-3">This Month Payments</div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_last_month_bill): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="mb-1">
            <span class="d-inline-block bgc-pink-l3 p-4 radius-round">
              <i class="fa fa-file text-pink-m2 text-180 w-4"></i>
            </span>
          </div>
          <div class="mt-1">
            <div class="text-secondary-d3 text-200 mt-4">RM <?= number_format($last_month_bill_amount['total_balance'], 2); ?></div>
            <div class="text-dark-tp4 text-110 mt-3">Last Month Bill Amount</div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_new_registrations): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="flex-grow-1 mt-4">
            <div class="text-secondary-d3 text-180"><?= $new_registration['total_amount'] ?></div>
            <div class="text-dark-tp4 text-110">New Registrations (Last 30 Days)</div>
          </div>
          <div class="mt-2 w-100">
            <canvas id="linechart-1" style="height: 60px; width: 100%;" class="ml-n1"></canvas>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_customer_account): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="text-secondary-d2 text-110 text-nowrap">Total Account</div>
          <div class="m-auto pt-2 w-75">
            <canvas id="piechart-1" style="height: 100px;"></canvas>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <!-- Sales last month and last month accounts -->
  <div class="row px-2 align-items-stretch">
    <?php if ($show_last_month_sales): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="mb-1">
            <span class="d-inline-block bgc-success-l2 p-4 radius-round">
              <i class="fa fa-dollar text-success-m1 text-180 w-4"></i>
            </span>
          </div>
          <div class="mt-1">
            <div class="text-secondary-d3 text-200 mt-4">RM <?= number_format($last_month_sales, 2); ?></div>
            <div class="text-dark-tp4 text-110 mt-3">Monthly Revenue (Up to <?= $last_month_end; ?>)</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="mb-1">
            <span class="d-inline-block p-4 radius-round" style="background-color:#95f0e8;">
              <i class="fa fa-dollar text-secondary-m1 text-180 w-4"></i>
            </span>
          </div>
          <div class="mt-1">
            <div class="text-secondary-d3 text-200 mt-4">RM <?= number_format($last_month_collection, 2); ?></div>
            <div class="text-dark-tp4 text-110 mt-3">Last Month Collection</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="mb-1">
            <span class="d-inline-block bgc-pink-l3 p-4 radius-round">
              <i class="fa fa-dollar text-pink-m2 text-180 w-4"></i>
            </span>
          </div>
          <div class="mt-1">
            <div class="text-secondary-d3 text-200 mt-4">RM <?= number_format($total_overdue, 2); ?></div>
            <div class="text-dark-tp4 text-110 mt-3">Overdue To Date</div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_last_month_accounts): ?>
      <div class="col-12 col-sm-6 col-md-3 px-2 mb-3 mb-md-0">
        <div class="bcard d-flex flex-column text-center px-2 py-3 h-100" style="min-height: 15rem; max-height: 15rem;">
          <div class="text-secondary-d2 text-110 text-nowrap">Last Month Activated</div>
          <div class="m-auto pt-2 w-75">
            <canvas id="piechart-2" style="height: 100px;"></canvas>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Trouble Tickets -->
  <?php if ($show_trouble_tickets): ?>
    <div class="mb-3 mt-lg-4">
      <div class="card bcard pt-1 pt-lg-2">
        <div class="card-header brc-primary-l3 d-flex justify-content-between align-items-center">
          <h5 class="card-title pl-1 text-150 text-600">Assigned And Created Service Tickets</h5>
          <button class="btn border-2 btn-outline-default btn-sm" onclick="ajax_view_more('', '', 'ticket')">View All</button>
        </div>

        <div class="card-body p-0 border-0">
          <div class="table-responsive-md" style="max-height: 25rem; min-height: 25rem; overflow-y: auto;">
            <table class="table table-striped-primary table-borderless border-0 mb-0">
              <tbody>
                <tr></tr>
                <?php if (!empty($assigned_ticket)): ?>
                  <?php foreach ($assigned_ticket as $ticket): ?>
                    <tr>
                      <td class="text-secondary-d2 pl-4 col-lg-3">
                        <?php if (!empty($ticket['tt_no'])): ?>
                          <a class="d-inline-block text-center mr-2 pt-2 w-5 h-5 radius-round bgc-primary-l2 text-primary font-bolder text-90" href="<?= base_url('ticket/edit_trouble_ticket/'.$ticket['tt_id']) ?>">
                            <?= $ticket['tt_no'] ?>
                          </a>
                        <?php endif; ?>
                      </td>
                      <td class="text-secondary font-bolder col-lg-4"><?= $ticket['customer_name'] ?></td>
                      <td class="text-grey-m1 col-lg-2"><?= $ticket['datetime_open'] ?></td>
                      <td class="text-grey-m1 text-center col-lg-1"><?= $ticket['status'] ?></td>
                      <td class="col-lg-2 text-center align-middle">
                        <div class="progress" style="height: 10px; width: 60%; margin: 0 auto;" data-toggle="tooltip" data-placement="top" data-html="true" title="Assigned to:<br><?= implode('<br>', $ticket['assigned_user_name']) ?>">
                          <div class="progress-bar <?= $ticket['design']['colorClass'] ?>" role="progressbar" style="width: <?= $ticket['design']['percentage'] ?>%; height: 10px;" aria-valuenow="<?= $ticket['design']['percentage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center align-middle text-muted" style="height: 25rem;">
                      <div class="text-120 font-weight-bold">No assigned service tickets</div>
                      <div class="text-90 text-secondary">You're all caught up for now!</div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- More Stats & Open Tickets -->
  <div class="row mt-2 mt-lg-4 pt-2">
    <?php if ($show_bill_and_payment_stats): ?>
      <div class="col-12 <?= !empty($_SESSION['acl']['trouble_ticket']) && in_array('A', $_SESSION['acl']['trouble_ticket']['actions']) ? 'col-lg-8' : 'col-lg-12' ?> mb-4 mb-lg-0">
        <div class="card bcard h-100">
          <div class="border-t-3 w-100 brc-info-m1 radius-t-1"></div>
          <div class="card-header">
            <h5 class="card-title text-grey-d1 pl-1">More Stats</h5>
          </div>
          <div class="card-body h-98 d-flex flex-column justify-content-center py-2 py-md-3 px-0 px-md-4">
            <canvas id="saleschart" class="mx-n1 mx-md-0 p-4" style="height: 300px;"></canvas>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_open_tickets_panel): ?>
      <div class="col-12 col-lg-4 mb-4 mb-lg-0">
        <div class="card bcard h-100">
          <div class="border-t-3 w-100 brc-success-m1 radius-t-1"></div>
          <div class="card-header border-0">
            <h5 class="card-title text-grey-d1 pl-1">Open Tickets</h5>
          </div>
          <div class="card-body p-3 d-flex flex-column h-100">
            <div id="open-ticket-list" class="flex-grow-1" style="max-height: 33.5rem; min-height: 33.5rem; overflow-y: auto;">
              <?php if (!empty($open_tickets)): ?>
                <?php foreach ($open_tickets as $index => $task): ?>
                  <div class="mb-2 text-grey-m1 clickable py-4" onclick="ajax_view_more('', '', '<?= 'ticket/edit_trouble_ticket/' . $task['id'] ?>')">
                    <div class="d-flex align-items-start mx-3">
                      <span class="d-inline-flex align-items-center justify-content-center w-8 h-8 radius-round mr-2px bgc-primary-l3 text-primary-d1 font-bolder text-90">
                        <i class="fa fa-ticket text-180"></i>
                      </span>
                      <div class="mx-2 text-grey-d1">
                        <div class="text-600 text-blue-d1 text-110"><?= $task['name'] ?></div>
                        <span class="text-90 text-secondary-m1"><?= $task['time'] ?></span>
                      </div>
                    </div>
                    <br>
                    <span class="pl-4 text-125 text-600"><?= $task['complaint_type'] ?></span>
                    <br>
                    <span class="pl-4 text-120"><?= $task['complaint'] ?></span>
                  </div>
                  <?php if ($index !== array_key_last($open_tickets)): ?>
                    <hr class="brc-grey-l3 m-0" />
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-center text-secondary-m1 bgc-secondary-l3 py-5 radius-1 mt-4">
                  <i class="fa fa-smile-beam text-150 text-blue-m2 mb-2"></i><br>
                  <span class="text-120">You're all caught up! No open tickets.</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="mt-2 px-4 border-t-1 brc-default-l3 pt-3">
              <button class="btn btn-block btn-sm border-2 btn-lighter-default btn-h-light-primary btn-a-light-primary" onclick="ajax_view_more('', '', 'ticket')">View All</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Manual Bills -->
  <?php if ($show_manual_bills): ?>
    <div class="mb-3 mt-lg-4">
      <div class="card bcard pt-1 pt-lg-2">
        <div class="card-header brc-primary-l3 d-flex justify-content-between align-items-center">
          <h5 class="card-title pl-1 text-150 text-600">Your Pending Manual Bills</h5>
          <button class="btn border-2 btn-outline-default btn-sm" onclick="ajax_view_more('', '', 'bill/bill_manual')">View All</button>
        </div>
        <div class="card-body p-0 border-0">
          <div class="table-responsive-md" style="max-height: 25rem; min-height: 25rem; overflow-y: auto;">
            <table class="table table-striped-primary table-borderless border-0 mb-0">
              <tbody>
                <tr></tr>
                <?php if (!empty($on_pending_manual_bills)): ?>
                  <?php foreach ($on_pending_manual_bills as $bill): ?>
                    <tr>
                      <td class="text-secondary-d2 pl-4 col-lg-3">
                        <a class="d-inline-block text-center mr-2 pt-2 w-5 h-5 radius-round bgc-primary-l2 text-primary font-bolder text-90" href="<?= base_url('bill/bill_manual_detail/' . $bill['bill_draft_no']) ?>">
                          # <?= $bill['bill_draft_no'] ?>
                        </a>
                      </td>
                      <td class="text-secondary font-bolder"><?= $bill['name'] ?></td>
                      <td class="text-grey-m1 col-lg-2"><?= $bill['date'] ?></td>
                      <td class="text-grey-m1 text-center col-lg-1"><?= $bill['status'] ?></td>
                      <td class="col-lg-2 pr-4 align-middle">
                        <div class="d-flex justify-content-between w-100 px-5">
                          <span>RM</span><span><?= $bill['total_amount'] ?></span>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center align-middle text-muted" style="height: 25rem;">
                      <div class="text-120 font-weight-bold">No Pending Manual Bills</div>
                      <div class="text-90 text-secondary">You're all caught up for now!</div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="mb-2 mt-3 text-right col-12 text-muted">Itelco</div>
</div>
