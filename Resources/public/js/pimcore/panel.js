pimcore.registerNS("feedbuilder.panel");
feedbuilder.panel = Class.create({

    initialize: function () {
    },
    getTabPanel: function() {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                border: false,
                layout: "border",
                items: [this.getTree(), this.getEditPanel()]
            });

            pimcore.layout.refresh();
        }

        return this.panel;
    },
    getTree: function() {
        if (!this.tree) {
            var store = Ext.create('Ext.data.TreeStore', {
                autoLoad: false,
                autoSync: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/feedbuilder/tree',
                    reader: {
                        type: 'json'
                    }
                },
                root: {
                    iconCls: "pimcore_icon_thumbnails"
                }
            });

            this.tree = new Ext.tree.TreePanel({
                store: store,
                region: "west",
                autoScroll:true,
                animate:false,
                containerScroll: true,
                width: 250,
                split: true,
                root: {
                    id: '0',
                    expanded: true
                },
                rootVisible: false,
                listeners: this.getTreeNodeListeners(),
                tbar: {
                    items: [
                        {
                            text: t("feedbuilder_add"),
                            iconCls: "pimcore_icon_add",
                            handler: this.addField.bind(this)
                        }
                    ]
                }
            });

            this.tree.on("render", function () {
                this.getRootNode().expand();
            });
        }

        return this.tree;
    },
    addField: function(){
        return {};
    },
    getTreeNodeListeners: function () {
        var treeNodeListeners = {
            'itemclick' : this.onTreeNodeClick.bind(this),
            //"itemcontextmenu": this.onTreeNodeContextmenu.bind(this),
            'beforeitemappend': function( thisNode, newChildNode, index, eOpts ) {
                newChildNode.data.leaf = true;
                newChildNode.data.expaned = true;
                newChildNode.data.iconCls = "pimcore_icon_sql"
            }
        };

        return treeNodeListeners;
    },
    onTreeNodeClick: function (tree, record, item, index, e, eOpts ) {
        this.openConfig(record.data.id);
    },
    getEditPanel: function() {
        if (!this.editPanel) {
            this.editPanel = new Ext.TabPanel({
                region: "center",
                plugins: ['tabclosemenu']
            });
        }

        return this.editPanel;
    },
    openConfig: function (id) {

        var existingPanel = Ext.getCmp("pimcore_sql_panel_" + id);
        if(existingPanel) {
            this.editPanel.setActiveTab(existingPanel);
            return;
        }

        Ext.Ajax.request({
            url: "/admin/feedbuilder/get",
            params: {
                id: id
            },
            success: function (response) {
                var data = Ext.decode(response.responseText);

                var fieldPanel = new feedbuilder.panelitem(data, this);
                pimcore.layout.refresh();
            }.bind(this)
        });
    },
});