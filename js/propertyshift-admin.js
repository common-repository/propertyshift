jQuery(document).ready(function($) {

    /********************************************/
    /* TRIGGER POST SUBMIT */
    /********************************************/
    $('.trigger-submit').on("click", function(e) {
        e.preventDefault();
        $('#publish').click();
    });

    /********************************************/
    /* LICENSE INPUT CHANGE */
    /********************************************/
    $(".license-key-input").on("change paste keyup", function() {
        var parent = $(this).closest('.ns-license-key');
        var activateButton = parent.find('.activate-license-button');
        activateButton.addClass('disabled');
        activateButton.attr("disabled", true);
        if(parent.find('.license-disabled-message').length == 0) {
            activateButton.closest('.admin-module-field').append('<div class="admin-module-note license-disabled-message">Enter a value above and then click <strong>Save changes</strong> to activate license key.</div>');
        }
    });

	/********************************************/
	/* REPEATERS (FLOOR PLANS, OPEN HOUSES, ETC.) */
	/********************************************/
	$('.repeater-container').on('click', '.add-repeater', function() {
	
        var count = $(this).closest('.repeater-container').find('.repeater-items > .ns-accordion').length;

		var repeaterItem = '\
            <div class="ns-accordion"> \
                <div class="ns-accordion-header"><i class="fa fa-chevron-right"></i> <span class="repeater-title-mirror floor-plan-title-mirror">'+ propertyshift_local_script.new_floor_plan +'</span> <span class="action delete delete-floor-plan"><i class="fa fa-trash"></i> '+ propertyshift_local_script.delete_text +'</span></div> \
    			<div class="ns-accordion-content floor-plan-item"> \
    				<div class="floor-plan-left"> \
    					<label>'+ propertyshift_local_script.floor_plan_title +' </label> <input class="repeater-title floor-plan-title" type="text" name="ps_property_floor_plans['+count+'][title]" placeholder="'+ propertyshift_local_script.new_floor_plan +'" /><br/> \
    					<label>'+ propertyshift_local_script.floor_plan_size +' </label> <input type="text" name="ps_property_floor_plans['+count+'][size]" /><br/> \
    					<label>'+ propertyshift_local_script.floor_plan_rooms +' </label> <input type="number" name="ps_property_floor_plans['+count+'][rooms]" /><br/> \
    					<label>'+ propertyshift_local_script.floor_plan_bathrooms +' </label> <input type="number" name="ps_property_floor_plans['+count+'][baths]" /><br/> \
    				</div> \
                    <div class="floor-plan-right"> \
                        <label>'+ propertyshift_local_script.floor_plan_description +' </label> \
    				    <textarea name="ps_property_floor_plans['+count+'][description]"></textarea> \
    				    <div class="floor-plan-img"> \
                            <label>'+ propertyshift_local_script.floor_plan_img +' </label> \
                            <input type="text" name="ps_property_floor_plans['+count+'][img]" /> \
                            <input id="_btn" class="ns_upload_image_button" type="button" value="'+ propertyshift_local_script.upload_img +'" /> \
                            <span class="button-secondary remove">'+ propertyshift_local_script.remove_text +'</span> \
                        </div> \
                    </div> \
                    <div class="clear"></div> \
    			</div> \
            </div> \
		';
	
        $(this).closest('.repeater-container').find('.repeater-items').append(repeaterItem);
        $(this).closest('.repeater-container').find('.no-floor-plan').hide();
    });
	
	$('.repeater-container').on('keypress keyup blur', '.repeater-title', function() {
		var mirrorTitle = $(this).parent().parent().prev().find('.repeater-title-mirror');
		if(mirrorTitle.html() == '') {
			mirrorTitle.html('Untitled');
		} else {
			mirrorTitle.html($(this).val());
		}
	});
	
	$('.repeater-container').on("click", ".delete", function() {
        $(this).parent().next().remove();
		$(this).parent().remove();
    });

});