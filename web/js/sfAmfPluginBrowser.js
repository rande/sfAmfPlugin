window.addEvent('domready', function() {
  result_views_menu.init();
  tree_view.init();
});

result_views_menu = {
  result_views_els: null,
  menu_el: null,

  init: function()
  {
    this.result_views_els = $$('.response .result_view');
    this.menu_el = $$('.response .result_views_menu')[0];

    menu_obj = this;

    //-- Create menu items
    $each(this.result_views_els, function(el_result_view) {
      var el_item = new Element('a', {
        'href': '#',
        'html': el_result_view.getElement('h5').get('html')
      });
      el_item.related_result_view = el_result_view;

      el_item.addEvent('click', function(e) {
        menu_obj.select_menu_item(this);
        e.preventDefault();
      });

      (el_li = new Element('li')).inject(menu_obj.menu_el, 'bottom');
      el_item.inject(el_li);

      if (el_result_view.hasClass('selected'))
        el_li.addClass('selected');
    });

    //-- Remove headings
    menu_obj.result_views_els.getElement('h5').setStyle('display', 'none');

    //-- Select the selected element
    this.select_menu_item(this.menu_el.getElement('.selected a'));
  },

  select_menu_item: function(menu_item)
  {
    this.result_views_els.removeClass('selected');
    menu_item.related_result_view.addClass('selected');
    menu_item.getParent('ul').getChildren().removeClass('selected');
    menu_item.getParent('li').addClass('selected');
  }
}

tree_view = {
  treeview_el: null,

  init: function()
  {
    this.treeview_el = $$('#tree_view table.tree_view')[0];
    this.treeview_el.getElements('tr').setStyle('display', 'none');
    this.treeview_el.getElements('tr.level0').setStyle('display', '');
    this.fold_node(this.treeview_el.getElement('tr.level0'));
  },

  toggle_node: function(el_tr)
  {
    if (el_tr.hasClass('is_open'))
      this.unfold_node(el_tr);
    else
      this.fold_node(el_tr);
  },

  fold_node: function(el_tr)
  {
    var parent_level = this._parseElementLevel(el_tr);
      
    var children_level = parent_level + 1;

    var el_tr_child = el_tr.getNext();

    while (el_tr_child && !el_tr_child.hasClass('level' + parent_level))
    {
      if (el_tr_child.hasClass('level' + children_level))
        el_tr_child.setStyle('display', '');

      el_tr_child = el_tr_child.getNext();
    }

    el_tr.addClass('is_open');
  },

  unfold_node: function(el_tr)
  {
    var parent_level = this._parseElementLevel(el_tr);

    var el_tr_child = el_tr.getNext();

    while (el_tr_child && !el_tr_child.hasClass('level' + parent_level))
    {
      el_tr_child.setStyle('display', 'none');
      el_tr_child = el_tr_child.getNext();
    }

    el_tr.removeClass('is_open');
  },

  _parseElementLevel: function(el_tr)
  {
    var regex = /^level(\d+)$/;

    return parseInt(el_tr
      .getProperty('class')
      .split(' ')
      .filter(function(item, index) { return item.match(regex); })[0]
      .replace(regex, '$1'));
  }
}