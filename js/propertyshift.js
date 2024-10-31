/************************************************************/
/* PROPERTYSHIFT */
/* FRONT-END SCRIPTS */
/************************************************************/

jQuery(document).ready(function($) {

	"use strict";

	/******************************************************************************/
	/** CREATE PRICE RANGE SLIDER  **/
	/******************************************************************************/
    var priceSliderCount = 1;
    $('.price-slider').each(function(i, obj) {
        $(this).attr( "data-count", priceSliderCount);
        $(this).closest('.filter-item').find('.price-min-label').attr('id', 'priceMinLabel'+priceSliderCount);
        $(this).closest('.filter-item').find('.price-max-label').attr('id', 'priceMaxLabel'+priceSliderCount);
        $(this).closest('.filter-item').find('.price-min-input').attr('id', 'priceMin'+priceSliderCount);
        $(this).closest('.filter-item').find('.price-max-input').attr('id', 'priceMax'+priceSliderCount);
        
        $(this).closest('.filter-item').find('.term-price-min').attr('id', 'termPriceMin'+priceSliderCount);
        $(this).closest('.filter-item').find('.term-price-max').attr('id', 'termPriceMax'+priceSliderCount);
        $(this).closest('.filter-item').find('.term-price-min-start').attr('id', 'termPriceMinStart'+priceSliderCount);
        $(this).closest('.filter-item').find('.term-price-max-start').attr('id', 'termPriceMaxStart'+priceSliderCount);
        
        priceSliderCount++;
    });

    function data ( element, key ) { return element.getAttribute('data-' + key); }

    function createSlider ( slider ) {

        var filter_price_min = parseInt(data(slider, 'min'));
        var filter_price_max = parseInt(data(slider, 'max'));
        var filter_price_min_start = parseInt(data(slider, 'min-start'));
        var filter_price_max_start = parseInt(data(slider, 'max-start'));

        //If term price, set term price values
        var termPriceMin = $(slider).closest('.filter-item').find('.term-price-min').val();
        if(termPriceMin != '' && termPriceMin != null) { 
            termPriceMin = parseInt(termPriceMin);
            filter_price_min = termPriceMin; 
        }

        var termPriceMax = $(slider).closest('.filter-item').find('.term-price-max').val();
        if(termPriceMax != '' && termPriceMax != null) { 
            termPriceMax = parseInt(termPriceMax);
            filter_price_max = termPriceMax; 
        }

        var termPriceMinStart = $(slider).closest('.filter-item').find('.term-price-min-start').val();
        if(termPriceMinStart != '' && termPriceMinStart != null) { 
            termPriceMinStart = parseInt(termPriceMinStart);
            filter_price_min_start = termPriceMinStart; 
        }

        var termPriceMaxStart = $(slider).closest('.filter-item').find('.term-price-max-start').val();
        if(termPriceMaxStart != '' && termPriceMaxStart != null) { 
            termPriceMaxStart = parseInt(termPriceMaxStart);
            filter_price_max_start = termPriceMaxStart; 
        }

        //Set currency options
        if(currency_symbol_position == 'before') {
          var pricePrefix = currency_symbol;
          var pricePostfix = '';
        } else {
          var pricePrefix = '';
          var pricePostfix = currency_symbol;
        }

        var priceSliderDirection = 'ltr';
        if(rtl == true) { priceSliderDirection = 'rtl'; } else { priceSliderDirection = 'ltr'; }
        
        noUiSlider.create(slider, {
                start: [filter_price_min_start, filter_price_max_start],
                connect: true,
                step: 1,
                margin:1,
                direction: priceSliderDirection,
                range: {
                    'min': filter_price_min,
                    'max': filter_price_max
                },
                format: wNumb({
                    // Set formatting
                    decimals: currency_decimal_num,
                    mark: currency_decimal,
                    thousand: currency_thousand,
                    prefix: pricePrefix,
                    postfix: pricePostfix
                 })
        });

        var count = Number(data(slider, 'count'));

        var nodes = [
            document.getElementById('priceMin'+count),
            document.getElementById('priceMax'+count) 
        ];

        slider.noUiSlider.on('update', function ( values, handle ) {
            nodes[handle].value = values[handle];
            $('#priceMinLabel'+count).html(values[0]);
            $('#priceMaxLabel'+count).html(values[1]);
        });

    }

    Array.prototype.forEach.call(document.querySelectorAll('.price-slider'), createSlider);


    /********************************************/
    /* MORTGAGE CALCULATOR WIDGET */
    /********************************************/
    ! function(e) {
        e.fn.homenote = function(t) {
            function r() {
                var t = e("#purchasePrice").val().replace(/[^0-9\.]/g, ""),
                    r = e("#dpamount").val().replace(/[^0-9\.]/g, "");
                if ("percentage" === p.dptype) return r / t * 100 + "%";
                var a = r / 100;
                return t * a
            }

            function a() {
                var t = e("#term").val();
                return "months" === e('input:radio[name="termtype"]:checked').val() ? 12 * t : t / 12
            }

            function n() {
                var t = e('input:radio[name="dptype"]:checked').val(),
                    r = e("#purchasePrice").val().replace(/[^0-9\.]/g, ""),
                    a = e("#dpamount").val().replace(/[^0-9\.]/g, "");
                if ("percentage" === t) {
                    var n = a / 100;
                    return r - r * n
                }
                return r - a
            }

            function l() {
                var t = e("#term").val();
                return "months" === e('input:radio[name="termtype"]:checked').val() ? t : 12 * t
            }

            function c() {
                var t = n(),
                    r = e("#rate").val().replace(/[^0-9\.]/g, "") / 100 / 12,
                    a = l(),
                    c = Math.pow(1 + r, a),
                    o = (t * (r * c / (c - 1))).toFixed(2);
                return o
            }

            function o() {
                var e = "<form id='homenote' role='form'>";
                return e += "<div class='form-group'><label for='purchasePrice'>"+ propertyshift_local_script.purchase_price +" (" + p.currencysym + ")</label>", e += "<input type='text' class='border' id='purchasePrice' value='" + p.principal + "'></div></hr>", e += "<div class='form-group'><label for='downPayment'>"+propertyshift_local_script.down_payment+"</label><input type='text' class='border' id='dpamount' value='" + p.dpamount + "'></div>", e += "<label class='label-radio'><input type='radio' name='dptype' id='downpercentage' value='percentage'", "percentage" === p.dptype && (e += " checked"), e += ">"+propertyshift_local_script.percent+" (%)</label>", e += "<label class='label-radio'><input type='radio' name='dptype' id='downlump' value='downlump'", "downlump" === p.dptype && (e += " checked"), e += ">" + p.currency + " (" + p.currencysym + ")</label><hr>", e += "<div class='form-group'><label for='rate'>"+propertyshift_local_script.rate+" (%)</label><input type='text' class='border' id='rate' value='" + p.rate + "'></div><hr>", e += "<div class='form-group'><label for='rate'>"+propertyshift_local_script.term+"</label><input type='text' class='border' id='term' value='" + p.term + "'></div>", e += "<label class='label-radio'><input type='radio' name='termtype' id='years' value='years' ", "years" === p.termtype && (e += "checked"), e += ">"+propertyshift_local_script.years+"</label>", e += "<label class='label-radio'><input type='radio' name='termtype' id='months' value='months'", "months" === p.termtype && (e += "checked"), e += ">"+propertyshift_local_script.months+"</label><hr>", e += "<div class='alert-box success' style='display:none;' id='results'></div>", e += "<button type='submit' class='button' id='calchomenote'>"+propertyshift_local_script.calculate+"</button></form>"
            }
            var p = e.extend({
                currencysym: currency_symbol,
                currency: propertyshift_local_script.fixed,
                termtype: "years",
                term: "30",
                principal: "250,000",
                dptype: "percentage",
                dpamount: "20%",
                rate: "6.5",
                resulttext: propertyshift_local_script.monthly_payment
            }, t);
            t = e.extend(p, t), e(document).on("change", 'input[name="termtype"]', function() {
                p.termtype = e(this).val(), e("#term").val(a())
            }), e(document).on("change", 'input[name="dptype"]', function() {
                p.dptype = e(this).val(), e("#dpamount").val(r())
            }), e(document).on("click", "#calchomenote", function(t) {

                var moneyFormat = wNumb({
                    decimals: currency_decimal_num,
                    mark: currency_decimal,
                    thousand: currency_thousand,
                });

                var totalNum = parseFloat(c());

                if(currency_symbol_position == 'before') {
                    var total = p.currencysym + moneyFormat.to(totalNum);
                } else {
                    var total = moneyFormat.to(totalNum) + p.currencysym;
                }

                t.preventDefault(), e("#results").hide().html(p.resulttext + " <strong>" + total + "</strong>").show()
            }), e(this).html(o())
        }
    }(jQuery);

    var moneyFormat = wNumb({
        decimals: currency_decimal_num,
        mark: currency_decimal,
        thousand: currency_thousand,
    });

    var principal = moneyFormat.to(200000);

    $('.mortgage-calculator-container').homenote({
        principal: principal,
    });

    /********************************************/
    /* FILTER MINIMAL */
    /********************************************/
    $('.filter-minimal .filter-minimal-advanced .price-hidden-input').prop('disabled', true);

    $('.filter-minimal .advanced-options-toggle').click(function() {
        var filterShortAdvanced = $(this).parent().parent().find('.filter-minimal-advanced');
        
        if(filterShortAdvanced.is(':hidden')) {
            $(this).parent().parent().find('.filter-minimal-advanced .price-hidden-input').prop('disabled', false);
        } else {
            $(this).parent().parent().find('.filter-minimal-advanced .price-hidden-input').prop('disabled', true);
        }

        filterShortAdvanced.slideToggle('fast');
    });

    $('.filter-minimal .filter-minimal-mobile-toggle').click(function() {
        $(this).parent().find('.filter-item').show();
        $(this).hide();
    });

    /******************************************************************************/
    /** FILTER - CHANGE PROPERTY STATUS  **/
    /******************************************************************************/
    $('.filter').on('change', '.property-status-dropdown', function() {
        var index = $(this).prop('selectedIndex');
        var tabIndex = index+1;
        var val = $(this).val();
        $(this).closest('.tabs').tabs( "option", "active", index );
        $(this).closest('.tabs').find('#tabs-'+tabIndex+' .property-status-dropdown').val(val);
        $(this).closest('.tabs').find('#tabs-'+tabIndex+' .property-status-dropdown').trigger("chosen:updated");
    });

    /******************************************************************************/
    /** SCROLL TO AGENT PROPERTIES **/
    /******************************************************************************/
    $(".button.agent-assigned").click(function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('a[name="anchor-agent-properties"]').offset().top
        }, 800);
    });

});