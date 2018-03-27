Ext.onReady(function () {
    Ext.define('User', {
        extend: 'Ext.data.Model',
        fields: [ 'ip', 'browser', 'system', 'url_first_from', 'url_last_to', 'url_unique_to' ]
    });

    var store = Ext.create('Ext.data.BufferedStore', {
        id: 'store',
        model: 'User',
        pageSize: 7,
        leadingBufferZone: 1000,
        proxy: {
            type: 'ajax',
            url: 'classes/grid.php',
            reader: {
                type: 'json',
                root: 'users',
                totalProperty: 'total'
            },
            simpleSortMode: true,
            filterParam: 'query'
        },
        remoteFilter: true
    });

    var grid = Ext.create('Ext.grid.Panel', {
        renderTo: document.body,
        store: store,
        width: 900,
        height: 200,
        title: 'User log',
        plugins: 'gridfilters',
        viewConfig: {
            emptyText: 'No data'
        },
        columns: [
            {
                xtype: 'rownumberer',
                width: 50,
                sortable: false
            },
            {
                text: 'IP',
                width: 100,
                hideable: false,
                dataIndex: 'ip',
                filter: {
                    type: 'string'
                },
                sortable: false
            },
            {
                text: 'Browser',
                flex: 1,
                dataIndex: 'browser'
            },
            {
                text: 'OS',
                width: 150,
                dataIndex: 'system'
            },
            {
                text: 'First came from url',
                width: 150,
                dataIndex: 'url_first_from',
                sortable: false
            },
            {
                text: 'Last visited url',
                width: 150,
                dataIndex: 'url_last_to',
                sortable: false
            },
            {
                text: 'Unique visited URLs number',
                width: 150,
                dataIndex: 'url_unique_to',
                sortable: false
            }
        ]
    });
});
