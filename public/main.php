<head>

    <style>
        #makeMeDraggable { width: 300px; height: 300px; background: red; }
    </style>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
    <script type="<text></text>/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
    <script type="text/javascript">

        $( init );

        function init() {
            $('#makeMeDraggable').draggable();
        }

    </script>

</head>

<body>
<div id="content" style="height: 400px;">
    <div id="makeMeDraggable"> </div>
</div>
</body>