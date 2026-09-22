<style type="text/css">

    @media (max-width: 576px) {

        #view-doc-modal .modal-dialog {
            width:93vw !important;
        }

        #view-doc-modal .modal_download_link {
            word-wrap: break-word;
        }

        #view-doc-modal .img_video_display {
            max-width: 83vw;
        }

        #view-doc-modal .pdf_display {
            width:83vw;
            height:60vh;
        }

        #view-doc-modal .embed_area {
            overflow-y: scroll;
            max-height: 56vh;
        }

    }

    @media (min-width:801px)  { 
    /* tablet, landscape iPad, lo-res laptops ands desktops */
        #view-doc-modal .modal-dialog {
            width:85vw !important;
        } 

        #view-doc-modal .img_video_display {
            max-width: 83vw;
        }

        #view-doc-modal .pdf_display {
            width:83vw;
            height:66vh;
        }

        #view-doc-modal .embed_area {
            overflow-y: scroll;
            max-height: 64vh;
        }

    }
    @media (min-width:1025px) { 
        /* big landscape tablets, laptops, and desktops */ 
    }
    @media (min-width:1281px) {
     /* hi-res laptops and desktops */ 
    }

    #view-doc-modal {
        top: -30px;
    }

    #view-doc-modal .close {
        top: -30px;
        position :relative;
    }
</style>
<div class="modal fade" id="view-doc-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">View Doc</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
            		<span aria-hidden="true">×</span>
          		</button>
            </div>
      
            <div class="modal-body">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    role="button" aria-disabled="false" data-dismiss="modal" style="margin:0.2em;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    async function checkLink(url) { return (await fetch(url)).ok }

    async function view_attach_doc(doc_id,category='') {
        $('#loading_symbol_attach_'+doc_id).css('display', '');
        $('#view-doc-modal .modal-body').html('');

        let categoryData = '';
        if(category != '') {
            categoryData = '[data-category="' + category + '"]';
        }

        let selector = $('.preview_area[data-id="' + doc_id + '"]' + categoryData);

        let preview_doc = selector.attr('data-local-path');
        let file_type = selector.attr('data-file-type');
        let extension = selector.attr('data-extension');
        let saved = selector.attr('data-saved');

        let filename = preview_doc.replace(/\.[^/.]+$/, "");

        if (file_type == "image") {
            $('#view-doc-modal .modal-body').html(`
                <div class="embed_area">
                <embed type="image/`+extension+`" src="`+preview_doc+`" class="img_video_display">
                </div>
                <br>
                <a class="modal_download_link" href="`+preview_doc+`" target="_BLANK">Download Full Image here:`+preview_doc+`</a>
                `);
        } else if (file_type == "pdf") {
            if (mobileCheck() && saved == '1') {
                let embed_html = `<div class="embed_area">`;
                let x = 0;
                let still_got_pages = true;
                while(still_got_pages && x < 999) {
                    let res = await checkLink(filename+'-'+x+'.jpg');
                    if (res) {
                        embed_html = embed_html + `<embed type="image/jpg" src="`+filename+`-`+x+`.jpg" class="img_video_display">`;
                    } else {
                        still_got_pages = false;
                    }
                    x++;
                }
                embed_html = embed_html + `</div><br><a class="modal_download_link" href="`+preview_doc+`" >Download PDF here:`+preview_doc+`</a>`;
                $('#view-doc-modal .modal-body').html(embed_html);
            } else {
                $('#view-doc-modal .modal-body').html(`
                    <embed type="application/pdf" src="`+preview_doc+`#view=FitH" class="pdf_display">
                    <br>
                    <a class="modal_download_link" href="`+preview_doc+`" >Download PDF here:`+preview_doc+`</a>
                    `);
            }
        } else if (file_type == "video") {
            $('#view-doc-modal .modal-body').html(`
                <embed type="video/`+extension+`" src="`+preview_doc+`" class="img_video_display">
                <br>
                <a class="modal_download_link" href="`+preview_doc+`" target="_BLANK">Download Video here:`+preview_doc+`</a>
                `);
        } else {
            $('#view-doc-modal .modal-body').html(`
                <a class="modal_download_link" href="`+preview_doc+`" target="_BLANK">Download File:`+preview_doc+`</a>
                `);
        }
        $('#loading_symbol_attach_'+doc_id).css('display', 'none');
        $('#view-doc-modal').modal('show');
    }

//move to general js if useful
window.mobileCheck = function() {
  let check = false;
  (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
  return check;
};
</script>