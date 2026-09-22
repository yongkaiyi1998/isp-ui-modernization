let current_ticket_ids = [];

function ajax_view_more(session_name, filter, redirect_url) {
  if(session_name == '' && filter == ''){
    window.location.href=base_url+redirect_url;
  }

	$.ajax({
		type: "POST",
		url: base_url + "home/view_more",
		data: { 
			session_name: session_name,
			filter: filter,
		},
		success: function (data) {
            data = JSON.parse(data);
			if(data.status == "success") {
               window.location.href=base_url+redirect_url;
            }else{
                $.gritter.add({
                    title: 'ERROR',
                    text: 'Something wrong has occured during view more.',
                    time: '5000',
                    close_icon: 'l-arrows-remove s16',
                    class_name: 'info-notice',
                });
            }
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

function refresh_dashboard() {

}

function tt_redirect_to(id) {
    if (!id) return;
    window.location.href = base_url + 'ticket/edit_trouble_ticket/' + id;
}

$(document).ready(function () {
  if(show_new_registrations) draw_registration_graph(new_registration);
  if(show_customer_account) draw_account_pie_chart(account_by_category);
  if(show_bill_and_payment_stats) draw_line_graph(total_sales_and_payment);
  if(show_last_month_accounts) draw_last_month_account_pie_chart(last_month_account_by_category);
  $('[data-toggle="tooltip"]').tooltip();
});

function draw_registration_graph(new_registration){
    const labels = new_registration.labels;
    const total_list = new_registration.total;
    const max_value = new_registration.max;
    const min_value = new_registration.min;

    new Chart(document.getElementById("linechart-1"), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
              type: 'line',
              label: '',
              
              data: total_list,
              
              spanGaps: true,

              lineTension: 0,
              backgroundColor: 'transparent',
              borderColor: 'rgba(93, 170, 201, 0.75)',
              borderWidth: 2,

              fill: true,
              backgroundColor : 'rgba(89, 182, 218, 0.25)',

              pointRadius: 5,
              pointBorderColor: 'transparent',
              pointBackgroundColor: 'transparent',
              pointBorderWidth: 5,

              pointHoverBackgroundColor: 'rgb(93, 170, 201)',
              pointHoverBorderColor: 'rgb(93, 170, 201)'
            }
        ]
    },
    options: {
      responsive: true,
      animation: {
         duration: 1000
      },
      legend: {
          display: false
      },
      scales: {
        yAxes: [
            {
                ticks: {
                    display: false,
                    min: min_value - 1, // minimum value
                    max: max_value + 1 // maximum value
                },
                gridLines: {
                  display: false,
                  drawBorder: false
                }
            }
        ],

        xAxes: [
          {
            barThickness: 4,
            gridLines: {
              display: false,
              drawBorder: false
            },
            ticks: {
                display: false //this will remove only the label
            }
          },
        ]
      },

      tooltips: {
        // Disable the on-canvas tooltip, because canvas area is small and tooltips will be cut (clipped)
        enabled: false,

        //use bootstrap tooltip instead
        custom: function(tooltipModel) {
          var title = '';
          var canvas = this._chart.canvas;

          if (tooltipModel.body) {
            title = tooltipModel.title[0] + ': ' + Number(tooltipModel.body[0].lines[0]).toLocaleString();
          }
          canvas.setAttribute('data-original-title', title);//will be used by bootstrap tooltip

          $(canvas)
          .tooltip({
            placement: 'bottom',
            template: '<div class="tooltip" role="tooltip"><div class="bgc-info-d2 tooltip-inner font-bolder text-110"></div></div>'
          })
          .tooltip('show')
          .on('hidden.bs.tooltip', function() {
            canvas.setAttribute('data-original-title', '');//so that when mouse is back over canvas's blank area, no tooltip is shown
          });
 
        }
      }//tooltips
    }
  })
}

function draw_last_month_account_pie_chart(last_month_account_by_category){
  const labels = last_month_account_by_category.labels;
  const total_list = last_month_account_by_category.total;

   new Chart(document.getElementById('piechart-2'), {
  type: 'doughnut',
  data: {
      datasets: [{
          label: 'Account Category',
          data: total_list,
          backgroundColor: [
              "#ea5d6a",
              "#718ff1",
              "#12d18f",
              "#ff7124",
              "#ffc688",
              "#9b59b6", 
              "#2ecc71", 
              "#f1c40f", 
              "#e67e22", 
              "#1abc9c", 
            ],
      }],
      labels: labels
  },
  
  options: {
      responsive: true,

      cutoutPercentage: 50,
      legend: {
          display: false
      },
      animation: {
         // animateScale: true,
          animateRotate: true,
          duration: 1000
      },
      tooltips: {
          enabled: true,
          cornerRadius: 0,
          bodyFontColor: '#fff',
          bodyFontSize: 14,
          fontStyle: 'bold',
          
          backgroundColor: 'rgba(34, 34, 34, 0.73)',
          borderWidth: 0,
         
          caretSize: 5,

          xPadding: 12,
          yPadding: 12,
          
          callbacks: {
            label: function(tooltipItem, data) {
              var label = data.labels[tooltipItem.index]
              return ' ' + label + ": " + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
            }
          }
      }
   }
 })
}

function draw_account_pie_chart(account_by_category){
  const labels = account_by_category.labels;
  const total_list = account_by_category.total;

   new Chart(document.getElementById('piechart-1'), {
  type: 'doughnut',
  data: {
      datasets: [{
          label: 'Account Category',
          data: total_list,
          backgroundColor: [
              "#ea5d6a",
              "#718ff1",
              "#12d18f",
              "#ff7124",
              "#ffc688",
              "#9b59b6", 
              "#2ecc71", 
              "#f1c40f", 
              "#e67e22", 
              "#1abc9c", 
            ],
      }],
      labels: labels
  },
  
  options: {
      responsive: true,

      cutoutPercentage: 50,
      legend: {
          display: false
      },
      animation: {
         // animateScale: true,
          animateRotate: true,
          duration: 1000
      },
      tooltips: {
          enabled: true,
          cornerRadius: 0,
          bodyFontColor: '#fff',
          bodyFontSize: 14,
          fontStyle: 'bold',
          
          backgroundColor: 'rgba(34, 34, 34, 0.73)',
          borderWidth: 0,
         
          caretSize: 5,

          xPadding: 12,
          yPadding: 12,
          
          callbacks: {
            label: function(tooltipItem, data) {
              var label = data.labels[tooltipItem.index]
              return ' ' + label + ": " + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
            }
          }
      }
   }
 })
}

function draw_line_graph(total_sales_and_payment){
  const payment_labels = total_sales_and_payment.payment.labels;
  const payment_total_list = total_sales_and_payment.payment.total_amounts;
  const sales_labels = total_sales_and_payment.sales.labels;
  const sales_total_list = total_sales_and_payment.sales.total_amounts;

  var canvas = document.getElementById("saleschart")
  var ctx = canvas.getContext("2d")

  var gradient1 = ctx.createLinearGradient(0, 0, 0, 300)
    gradient1.addColorStop(0, 'rgba(234, 93, 106, 0.3)')   // light red
    gradient1.addColorStop(1, 'rgba(234, 93, 106, 0)')

  var gradient2 = ctx.createLinearGradient(0, 0, 0, 250)
    gradient2.addColorStop(0, 'rgba(113, 143, 241, 0.4)')  // light blue
    gradient2.addColorStop(1, 'rgba(113, 143, 241, 0)')

  var gradients = [];
     gradients.push(gradient1, gradient2)

  var chartOptions1 = {
    lineTenstion: 0.3,
    borderWidth: 2,
    pointRadius: 2
  }

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: payment_labels,
      datasets: [
        {
          label: "Payment",
          data : payment_total_list,

          borderColor: 'rgba(234, 93, 106, 0.7)',
          pointBorderColor: 'rgba(234, 93, 106, 0.67)',

          fill: true,
          backgroundColor : gradients[0],
          pointBackgroundColor: '#FFF',

          borderWidth: chartOptions1.borderWidth,
          pointRadius: chartOptions1.pointRadius,
          lineTension: chartOptions1.lineTension,
        },
        {
          label: "Sales",
          data: sales_total_list,

          borderColor: 'rgba(113, 143, 241, 0.7)',
          pointBorderColor: 'rgba(113, 143, 241, 0.67)',

          fill: true,
          backgroundColor : gradients[1],
          pointBackgroundColor: '#FFF',

          borderWidth: chartOptions1.borderWidth,
          pointRadius: chartOptions1.pointRadius,
          lineTension: chartOptions1.lineTension,
        }
      ]
    },
    options: {
      responsive: true,
      animation: {
        duration: 1000
      },
      tooltips: {
        mode: 'index',
        enabled: true,
        cornerRadius: 0,
        titleFontColor: 'rgba(0, 0, 0, 0.8)',
        titleFontSize: 16,
        titleFontStyle: 'normal',
        bodyFontColor: 'rgba(0, 0, 0, 0.8)',
        bodyFontSize: 14,
        fontFamily: 'Open Sans',
        backgroundColor: 'rgba(255, 255, 255, 0.73)',
        borderWidth: 2,
        borderColor: 'rgba(254, 224, 116, 0.73)',
        caretSize: 5,
        xPadding: 12,
        yPadding: 12,
        callbacks: {
          label: function(context) {
            const label = context.dataset?.label || '';
            const value = context.raw ?? context.yLabel ?? 0;

            const formattedValue = Number(value).toLocaleString('en-MY', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });

            return `${label}: RM ${formattedValue}`;
          }
        }
      },
      scales: {
        yAxes: [{
          ticks: {
            fontFamily: "Open Sans",
            fontColor: "#85808e",
            fontStyle: "bolder",
            fontSize: "13",
            beginAtZero: false,
            maxTicksLimit: 3,
            padding: 16
          },
          gridLines: {
            drawTicks: false,
            display: false
          }
        }],
        xAxes: [{
          gridLines: {
            zeroLineColor: "transparent"
          },
          ticks: {
            fontFamily: "Open Sans",
            fontColor: "#85808e",
            fontSize: "14",
            padding: 12
          }
        }]
      },
      legend: {
        display: true,
        position: 'bottom'
      }
    }
  })
}

setInterval(() => {
  if(show_open_tickets_panel) updateOpenTicket();
}, 60000); // 1 minute

function updateOpenTicket() {
  $.ajax({
    type: "GET",
    dataType: "JSON",
    url: base_url + "home/ajax_get_open_ticket",
    success: function (data) {
      const container = $("#open-ticket-list");
      container.empty();

      if (Array.isArray(data) && data.length > 0) {
        const new_ids = data.map(t => t.id);
        const new_ticket_detected = current_ticket_ids.length && !current_ticket_ids.includes(new_ids[0]);

        if (new_ticket_detected) {
          $.gritter.add({
            title: '⚠️ New Open Ticket!',
            text: 'You have a new ticket that needs attention.',
            time: '5000',
            close_icon: 'l-arrows-remove s16',
            class_name: 'info-notice',
          });
        }

        current_ticket_ids = new_ids;

        data.forEach((task, index) => {
          const isLast = index === data.length - 1;
          const ticketHTML = `
            <div class="mb-2 text-grey-m1 clickable py-4" onclick="ajax_view_more('', '', 'ticket/edit_trouble_ticket/${task.id}')">
              <div class="d-flex align-items-start mx-3">
                <span class="d-inline-flex align-items-center justify-content-center w-8 h-8 radius-round mr-2px bgc-primary-l3 text-primary-d1 font-bolder text-90">
                  <i class="fa fa-ticket text-180"></i>
                </span>
                <div class="mx-2 text-grey-d1">
                  <div class="text-600 text-blue-d1 text-110">${task.name}</div>
                  <span class="text-90 text-secondary-m1">${task.time}</span>
                </div>
              </div>
              <br>
              <span class="pl-4 text-125 text-600">${task.complaint_type}</span>
              <br>
              <span class="pl-4 text-120">${task.complaint}</span>
            </div>
            ${!isLast ? '<hr class="brc-grey-l3 m-0" />' : ''}
          `;
          container.append(ticketHTML);
        });
      } else {
        container.html(`
          <div class="text-center text-secondary-m1 bgc-secondary-l3 py-5 radius-1 mt-4">
            <i class="fa fa-smile-beam text-150 text-blue-m2 mb-2"></i><br>
            <span class="text-120">You're all caught up! No open tickets.</span>
          </div>
        `);
        current_ticket_ids = [];
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error("Failed to fetch open tickets:", thrownError);
    }
  });
}
