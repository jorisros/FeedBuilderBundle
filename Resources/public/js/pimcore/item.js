pimcore.registerNS("feedbuilder.panelitem");

feedbuilder.panelitem = Class.create({
    initialize: function (data, parentPanel) {
        console.log(data);
        this.parentPanel = parentPanel;
        this.data = data;
        this.addLayout();
    },
    buildForm: function() {
        this.form = new Ext.form.FormPanel({
            border: false,
            items: [{
                xtype: "fieldset",
                itemId: "generalFieldset",
                title: t("feedbuilder_fieldset_title"),
                collapsible: false,
                defaults: {
                    width: 400
                },
                items: [{
                    xtype: "textfield",
                    name: "name",
                    value: this.data.title,
                    fieldLabel: t("feedbuilder_form_title"),
                    disabled: true
                    },
                    this.getChannelCombo()

                ]
            },
                {
                    xtype: "fieldset",
                    itemId: "selectionFieldset",
                    title: t("feedbuilder_fieldset_selection_title"),
                    collapsible: false,
                    defaults: {
                        width: 400
                    },
                    items: [

                        this.getdrop()

                    ]
                }]
        });
        return this.form;
    },
    getdrop: function() {
        var href = {
            name: 'ss',
            fieldLabel: t("feedbuilder_form_path")
        };

        this.component = new Ext.form.TextField(href);

        this.component.on("render", function (el) {

            // add drop zone
            new Ext.dd.DropZone(el.getEl(), {
                reference: this,
                ddGroup: "element",
                getTargetFromEvent: function (e) {
                    return this.reference.component.getEl();
                },

                onNodeOver: function (target, dd, e, data) {

                    var record = data.records[0];
                    var data = record.data;

                    if(data.elementType == 'object') {
                        return Ext.dd.DropZone.prototype.dropAllowed;
                    }else{
                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                    }

                }.bind(this),

                onNodeDrop: this.onNodeDrop.bind(this)
            });


            //el.getEl().on("contextmenu", this.onContextMenu.bind(this));

        }.bind(this));

        // disable typing into the textfield
        this.component.on("keyup", function (element, event) {
            element.setValue(this.data.path);
        }.bind(this));
        
        return this.component;
    },
    onNodeDrop: function (target, dd, e, data) {
        console.log(data);
        var record = data.records[0];
        var data = record.data;

        if(data.elementType == 'object') {
            this.component.setValue(data.path);
            return true;
        }else{
            return false;
        }
    },
    getChannelCombo: function() {

        var store = new Ext.data.JsonStore({
            storeId: 'myStore',
            proxy: {
                type: 'ajax',
                url: '/admin/feedbuilder/channel',
                reader: {
                    type: 'json',
                    rootProperty: 'list'
                }
            }
        });

        return new Ext.form.ComboBox({
            name: 'list',
            fieldLabel: t('feedbuilder_form_channel'),
            valueField: 'abbr',
            displayField: 'name',
            renderTo: Ext.getBody(),
            triggerAction: 'all',
            store: store
        });
    },
    addLayout: function() {
        var panelButtons = [];
        panelButtons.push({
            text: t("save"),
            iconCls: "pimcore_icon_apply",
            handler: this.save.bind(this)
        });

        this.panel = new Ext.Panel({
            region: "center",
            id: "pimcore_sql_panel_" + this.data.title,
            labelWidth: 150,
            autoScroll: true,
            border: false,
            items: [
                this.buildForm()
                //this.getGeneralDefinitionPanel(),
                //this.getSourceDefinitionPanel(),
               // this.columnGrid,
                //this.getChartDefinitionPanel()
            ],
            buttons: panelButtons,
            title: this.data.title,
            bodyStyle: "padding: 20px;",
            closable: true,
            listeners: {
            //    afterrender: this.getColumnSettings.bind(this)
            }
        });

        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();
    },
    save: function() {
        return {};
    }

});