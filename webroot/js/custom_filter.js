(function( $ ) {
	$.fn.extend({
        displayVal: function() {
			var $label;
			if($(this).is('select')){
				return $('option:selected',this).text();
			}else if($(this).is('input[type=checkbox]') && ($label = $('label[for='+$(this).attr('id')+']',$(this).closest('form'))).length){
				return $(this).is(':checked')?$label.text():'';
			}else{
				return $(this).val();
			}
        }
    });

	function resize(){
		if($.colorbox){
			$.colorbox.resize();
		}
	}
	
	function updateField(){
		var $form = $(".customFilterCondForm.active");
		var $filterForm = $form.closest('.customFiltersForm');
		var $FieldField = $('.customFilterCondField',$form);
		if(window.console){
			console.log($FieldField);
		}
		if($FieldField.length){
			var model = $('#CustomFilterModel',$filterForm).val();
			var pos = $form.attr('pos');
			var field = $FieldField.val();
			if($('.FilterType',$form).attr('for_field') != field){
				$('.FilterType',$form).attr('for_field',field);
				if(field.length){
					$('#CustomFilterGroup',$form).val($('option:selected',$FieldField).text());
					$('#CustomFilterGroupField',$form).val(field);
					$('.FilterType',$form).empty();
					$('.FilterType',$form).addClass('loading');
					$('.FilterType',$form).load($('.FilterType',$form).attr('source').replace('%model%',model).replace('%field%',field).replace('%order%',pos),function(){
						$('.FilterType',$form).removeClass('loading');
						updateOperator();
						$('.FilterType input',$form).trigger("updateScript");
						resize();
						updateLabel();
					});
				}else{
					$('.FilterType',$form).empty();
					resize();
				}
			}else{
				updateOperator();
			}
		}
	}
	function updateOperator(){
		var $form = $(".customFilterCondForm.active");
		var $operatorField = $('.customFilterCondOp',$form);
		if($operatorField.length){
			var $defaultInput = $(".operationVariantes .variante_default .customFilterCondVal1",$form);
			if(!$defaultInput.length) $defaultInput = $(".customFilterCondVal1",$form);
			var $defaultLabel = $('label',$defaultInput.parent());
			$defaultLabel.text($operatorField.displayVal());
			
			var $variante = $(".operationVariantes .variante_"+$operatorField.val(),$form);
			if(!$variante.length) $variante = $(".operationVariantes .variante_default",$form);
			if(window.console){
				console.log($variante);
			}
			$(".operationVariantes .variante").hide().find('input, textarea, select').attr('disabled',true);
			$variante.show().find('input, textarea, select').removeAttr('disabled');
			resize();
		}
	}
	
	function updateLabel(){
		var $form = $(".customFilterCondForm.active");
		var $filterForm = $form.closest('.customFiltersForm');
		var label = '';
		var shortLabel = '';
		var $valField = $('.customFilterCondVal1:not([disabled])',$form);
		//if($valField.val()){
			if($('.customFilterCondNot:checked',$form).length){
				var notStr = $('.customFilterCondNot',$form).displayVal()+' : '
				label += notStr;
				shortLabel += notStr;
			}
			if($('.customFilterCondField',$form).val()){
				label += $('.customFilterCondField',$form).displayVal()+' ';
			}
			if($('.customFilterCondOp',$form).length){
				var opStr = $('.customFilterCondOp',$form).displayVal()+' ';
				label += opStr.toLowerCase();
				if($('.customFilterCondOp',$form).val() != 'Equals'){
					shortLabel += opStr;
				}
			}
			if($valField.displayVal()){
				label += $valField.displayVal();
				shortLabel +=  $valField.displayVal();
			}
			if($('.customFilterCondVal2:not([disabled])',$form).length){
				var val2Str = ' '+str_and.toLowerCase() +' '+ $('.customFilterCondVal2:not([disabled])',$form).displayVal();
				label += val2Str;
				shortLabel += val2Str;
			}
			$('input[ref=customFilterCondTile]',$form).val(label);
			var $menuItem = getCondMenuItem($form);
			var pos = $menuItem.prevAll().length+1;
			$('a.customFilterCondEdit',getCondMenuItem($form)).text('#' + pos + ' - ' + label);
			if(condCount() == 1 && $('.customFilterCondField',$form).val()){
				setSaveDefaults(shortLabel,$('.customFilterCondField',$form).displayVal(),$('#CustomFilterModel',$filterForm).val()+'::'+$('.customFilterCondField',$form).val());
			}else{
				setSaveDefaults(null,null,null);
			}
		//}
	}
	
	function setSaveDefaults(title,group,groupKey){
		var $filterForm = $('.customFiltersForm');
		if(!$('#CustomFilterTitle',$filterForm).val() || $('#CustomFilterTitle',$filterForm).attr('auto') == $('#CustomFilterTitle',$filterForm).val()){
			$('#CustomFilterTitle',$filterForm).val(title);
		}
		$('#CustomFilterTitle',$filterForm).attr('auto',title);
		if(!$('#CustomFilterGroupTitle',$filterForm).val() || $('#CustomFilterGroupTitle',$filterForm).attr('auto') == $('#CustomFilterGroupTitle',$filterForm).val()){
			$('#CustomFilterGroupTitle',$filterForm).val(group);
		}
		$('#CustomFilterGroupTitle',$filterForm).attr('auto',group);
		$('#CustomFilterGroupKey',$filterForm).val(groupKey);
	}
	
	function updateSaveField(){
		if($('#CustomFilterSave:checked').length>0 != $(".customFiltersForm .saveFields:visible").length>0){
			if($('#CustomFilterSave:checked').length){
				$(".customFiltersForm .saveFields").show();
			}else{
				$(".customFiltersForm .saveFields").hide();
			}
			resize();
		}
	}
	
	function condCount(){
		var $filterForm = $('.customFiltersForm');
		return $('.customFilterCondEditor > .customFilterCondForm',$filterForm).length
	}
	
	function addCond(){
		var $filterForm = $('.customFiltersForm');
		var pos = condCount();
		var $clone = $('.customFilterCondModel',$filterForm).clone();
		$clone = $('<div>'+$clone.html().replace(/%%i%%/g,pos)+'</div>');
		var $new = $('.customFilterCondForm',$clone);
		$('.customFilterCondEditor',$filterForm).append($new);
		$('.customFilterCondList ul',$filterForm).append($('.customFilterCondMenuItem',$clone));
		setActiveCond($new);
		updateLabel();
	}
	
	function setActiveCond($cond){
		var $filterForm = $('.customFiltersForm');
		$cond = $($cond);
		$('.customFilterCondForm',$filterForm).removeClass('active');
		$cond.addClass('active');
		$('.customFilterCondMenuItem',$filterForm).removeClass('active');
		getCondMenuItem($cond).addClass('active');
		resize();
	}
	
	function removeCond($cond){
		var $filterForm = $('.customFiltersForm');
		$cond = $($cond);
		var $next;
		if($cond.is(".active")){
			if($cond.nextAll('.customFilterCondForm').first().length){
				$next = $cond.nextAll('.customFilterCondForm').first();
			}else if($cond.prevAll('.customFilterCondForm').first().length){
				$next = $cond.prevAll('.customFilterCondForm').first();
			}
			if(window.console){
				console.log($next);
				console.log($cond.prevAll('.customFilterCondForm'));
			}
		}
		getCondMenuItem($cond).remove();
		if($("input[ref=customFilterCondId]",$cond).length){
			$cond.replaceWith('<input type="hidden" name="data[CustomFilterCond][delete][]" value="'+$("input[ref=customFilterCondId]",$cond).val()+'">');
		}else{
			$cond.remove();
		}
		if($next){
			setActiveCond($next);
		}
	}
	
	function getCondMenuItem($cond){
		$cond = $($cond);
		var $filterForm = $cond.closest('.customFiltersForm');
		return $('.customFilterCondList .customFilterCondMenuItem[for='+$cond.attr('id')+']',$filterForm);
	}
	
	$(function(){
		if($.colorbox){
			var colorBoxOpt = {
				'trapFocus': false,
				'scrolling' : false,
				'onComplete' : function(){
					var match = $('#cboxLoadedContent').html().match(/^[0-9]*$/);
					if(match) {
						var url = $('.customFilters').attr('url');
						var param = url.match(/filters:([0-9,])+/);
						$.colorbox.close();
						if(param){
							document.location.href = url.replace(param[0],'filters:'+match[0]);
						}else{
							document.location.href = url + '/filters:'+match[0];
						}
						return false;
					}
					$('#cboxLoadedContent input').trigger("updateScript");
					$(".customFiltersForm").ajaxForm({'beforeSubmit':function(data, jqForm, options) { 
						$.colorbox($.extend({href:options.url,data:data},colorBoxOpt));
						return false;
					}});
					updateField();
					updateSaveField();
				}
			};
			$('.customFilterAddButton, .customFilterBtEdit').colorbox(colorBoxOpt);
		}
		$('body').on('change','.customFilterCondField',function (){
			updateField()
		});
		$('body').on('change','#CustomFilterSave',function (){
			updateSaveField();
		});
		updateField();
		updateSaveField();
		
		$('body').on('change','.customFilterCondOp',function (){
			updateOperator();
		});
		
		$('body').on('change','.customFilterCondField, .customFilterCondNot, .customFilterCondOp, .customFilterCondVal1, .customFilterCondVal2',function (){
			updateLabel();
		});
		
		$('body').on('click','.customFilterCondAdd',function (e){
			addCond();
			e.preventDefault();
		});
		$('body').on('click','.customFilterCondEdit',function (e){
			setActiveCond('#'+$(this).closest('.customFilterCondMenuItem').attr('for'));
			e.preventDefault();
		});
		$('body').on('click','.customFilterCondDelete',function (e){
			removeCond('#'+$(this).closest('.customFilterCondMenuItem').attr('for'));
			e.preventDefault();
		});
	})
})( jQuery );