/*
* jquery.showhide-rules-1.2.js
* http://jquery-plugin-showhide-rules.googlecode.com
*
*/
(function ($) {

    var methods = {
        init: function (options) {

            var rules = new Array();
            var formulas = new Array();
            if (options != undefined) {
                if (options.showhide) {
                    rules = options.showhide;
                }
                if (options.formula) {
                    formulas = options.formula;
                }
                if (options.inline) {
                    $('.sh-child').each(function () {
                        if ($(this).data('showhide').dependencies != undefined) {
                            var childId = $(this).attr('id');
                            var orStatements = $(this).data('showhide').dependencies;
                            rules.push([childId, orStatements]);
                        }
                    });
                }
				if (options.parentStatement) {
					$(this).each(function () {
						var itemID = $(this).attr('id');
						if (!itemID) {
							itemID = 'id-' + (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
							$(this).attr('id', itemID);
						}
						rules.push([itemID, options.parentStatement]);
					});
				}
            }

            for (x in rules) {
                var childId = rules[x][0];
                var rule = rules[x][1];
                $('#' + childId).data('showhide', { dependencies: rule,
                    dependenciesMet: ''
                });
                var orStatements = rule.split(' ');
                for (y in orStatements) {
                    var andPieces = orStatements[y].split('_AND_').sort();
                    for (z in andPieces) {
                        var parentId = andPieces[z];
                        var childClass = andPieces[z];
                        if (parentId.substr(0, 5) == 'NOT_') {
                            parentId = parentId.substr(5);
                        }
                        if (parentId.indexOf('_VAL_') != -1) {
                            parentId = parentId.substr(0, parentId.indexOf("_VAL_"));
                        }
                        $('#' + childId).addClass('sh-' + childClass + '-child');
						var parent = $('#' + parentId);
						if (parent.attr('type') == 'radio' && !parent.hasClass('sh-parent-item')) {
							$('input[name="' + parent.attr('name') + '"]').each(function () {
								$(this).addClass('sh-parent-item');
							});
						} else {
							parent.addClass('sh-parent-item');
						}
                    }
                }
            }
            for (x in formulas) {
                var childId = formulas[x][0];
                var formula = formulas[x][1];
                $('#' + childId).data('formula', { formula: formula });
                var parentIds = formula.split(/[\+\-\/\*]+/);
                for (y in parentIds) {
                    var parentId = parentIds[y];
                    if (!Number(parentId)) {
                        $('#' + childId).addClass('sh-' + parentId + '-child');
                        $('#' + parentId).addClass('sh-parent-item');
                    }
                }
            }

            $('.sh-parent-item').each(function () {
                methods.typifyParent.call($(this), 'init');
            });
			$(this).on('change', '.sh-parent-item', function () {
                methods.typifyParent.call($(this), 'change');
            });
        },
        typifyParent: function (event) {
            if ($(this).attr('type') == 'radio') {
                var checked = $(this).is(':checked');
                if (event == 'change') {
                    $('input[name="' + $(this).attr('name') + '"]').each(function () {
                        methods.processParent.call($(this), false, "");
                    });
                }
                methods.processParent.call($(this), checked, '');
            } else if ($(this).attr('type') == 'checkbox') {
                var checked = $(this).is(':checked');
                methods.processParent.call($(this), checked, '');
            } else if ($(this).is('select')) {
                var thisSelect = $(this);
                $('option', this).each(function () {
                    methods.processParent.call(thisSelect, false, $(this).val());
                });
                methods.processParent.call($(this), true, $(this).val());
            } else if ($(this).attr('type') == 'text') {
                var parentId = $(this).attr('id');
                var childClass = 'sh-' + parentId + '-child';
                $('.' + childClass).each(function () {
                    if ($(this).data('formula') !== undefined) {
                        var newVal = methods.evalFormula.call($(this), $(this).data('formula').formula);
                        if (newVal != "NaN") {
                            $(this).val(newVal);
                            $(this).blur();
                        }
                    }
                });
            }
        },
        processParent: function (checked, value) {

            return this.each(function () {
                if (value != '') {
                    value = '_VAL_' + value;
                }

                var parentId = $(this).attr('id') + value;
                var notParentID = 'NOT_' + parentId;
                var childClass = 'sh-' + parentId + '-child';
                var notChildClass = 'sh-NOT_' + parentId + '-child';

                if (checked) {
                    $('.' + childClass).each(function () {
                        methods.addDependenciesMet(this, parentId);
                        methods.showHideChild(this);
                    });
                    $('.' + notChildClass).each(function () {
                        methods.removeDependenciesMet(this, notParentID);
                        methods.showHideChild(this);
                    });
                } else {
                    $('.' + childClass).each(function () {
                        methods.removeDependenciesMet(this, parentId);
                        methods.showHideChild(this);
                    });
                    $('.' + notChildClass).each(function () {
                        methods.addDependenciesMet(this, notParentID);
                        methods.showHideChild(this);
                    });
                }
            });
        },
        showHideChild: function (child) {
            if (methods.areDependenciesMet(child)) {
                $(child).show();
            } else {
                $(child).hide();
            }
        },
        addDependenciesMet: function (child, parentId) {
            var newDependenciesMet = parentId;
            if (($(child).data('showhide').dependenciesMet != undefined) && ($(child).data('showhide').dependenciesMet)) {
                var oldDependenciesMet = $(child).data('showhide').dependenciesMet;
                if (oldDependenciesMet.indexOf(newDependenciesMet) != -1) {
                    newDependenciesMet = oldDependenciesMet;
                } else {
                    newDependenciesMet = oldDependenciesMet + " " + parentId;
                }
            }
            $.extend($(child).data('showhide'), { dependenciesMet: newDependenciesMet });
        },
        removeDependenciesMet: function (child, parentId) {
            var newDependenciesMet = "";
            if ($(child).data('showhide').dependenciesMet != undefined) {
                var newDependenciesMet = $(child).data('showhide').dependenciesMet.split(' ');
                for (x in newDependenciesMet) {
                    if (newDependenciesMet[x] == parentId) {
                        newDependenciesMet.splice(x, 1);
                        break;
                    }
                }
                newDependenciesMet = newDependenciesMet.join(" ");
            }
            $.extend($(child).data('showhide'), { dependenciesMet: newDependenciesMet });
        },
        areDependenciesMet: function (child) {
            if ($(child).data('showhide').dependencies == undefined || $(child).data('showhide').dependenciesMet == undefined) {
                return false;
            }
            var orStatements = $(child).data('showhide').dependencies.split(" ");
            var dependenciesMet = $(child).data('showhide').dependenciesMet.split(" ");
            var pass = false;

            for (y in orStatements) {
                var andPieces = orStatements[y].split('_AND_').sort();
                for (z in andPieces) {
                    if (jQuery.inArray(andPieces[z], dependenciesMet) == -1) {
                        pass = false;
                        break;
                    }
                    pass = true;
                }
                if (pass == true) {
                    return true;
                }
            }
            return pass;
        },
        evalFormula: function (formula) {
            if (!Number(formula)) {
                var formulaParts = formula.split(/[\+\-\/\*]+/);
                for (x in formulaParts) {
                    if (!Number(formulaParts[x])) {
                        var idVal = $('#' + formulaParts[x]).val();
                        if (Number(idVal)) {
                            formula = formula.replace(formulaParts[x], idVal);
                        } else {
                            return "NaN";
                        }
                    }
                }
            }
            return eval(formula);
        }
    }

    $.fn.showhide = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.showhide');
            return false;
        }

    };

})(jQuery);