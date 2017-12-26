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
            }]
        });

        return this.form;
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