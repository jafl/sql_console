<?php

# This code is in the public domain.

#
# Configuration
#

# database connection

$db_config = array
(
	'url'  => 'mysql:host=127.0.0.1',
	'user' => 'root',
	'pass' => ''
);

# maximum number of results to save in scrolling output pane

$line_buffer_length = 100;

#
# END Configuration
#

$pdo = new PDO(
	$db_config['url'], $db_config['user'], $db_config['pass'],
	array( PDO::ATTR_PERSISTENT => true ));
$pdo->exec('SET CHARACTER SET utf8');

if (isset($_POST['cmd']))
{
	if (isset($_POST['db']) && preg_match('/^[a-z0-9_]+$/i', $_POST['db']))
	{
		$pdo->exec('use '.$_POST['db']);
	}

	$s = $pdo->query($_POST['cmd']);
	if ($s === false)
	{
		$error  = $pdo->errorInfo();
		$result = array( 'error' => $error[2] );
	}
	else
	{
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
	}

	header('Content-type: application/json');
	echo json_encode($result);
	exit;
}

$db_list = array();

$s = $pdo->query('show databases');
foreach ($s->fetchAll(PDO::FETCH_COLUMN, 0) as $name)
{
	$s1 = $pdo->query('show tables from '.$name);
	if ($s1)
	{
		$db_list[ $name ] = $s1->fetchAll(PDO::FETCH_COLUMN, 0);
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>MySQL Console</title>

	<script src="http://yui.yahooapis.com/3.8.0/build/yui/yui-min.js"></script>

	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.8.0/build/cssreset/reset-min.css" />
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.8.0/build/cssfonts/fonts-min.css" />
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.8.0/build/cssbase/base-min.css" />

	<style type="text/css">
	.yui3-js-enabled .yui3-widget-loading { visibility:hidden; position:absolute; top:-10000px; left:-10000px; }

	.layout-hd,.layout-ft {text-align:center;}
	.layout-hd h1 {font-size:150%;font-weight:bold;margin:0;padding:5px 0;}

	.layout-bd .layout-module-col {padding:0 5px;}
	.layout-bd .layout-module {margin:5px 0;border-radius:10px;}
	.layout-bd .layout-m-hd {background-color:#EEE;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom:1px solid #666;}
	.layout-bd .layout-m-ft {text-align:center;background-color:#EEE;border-top:1px solid #666;}
	.layout-bd .layout-m-ft,
	.layout-bd .layout-collapsed-vert .layout-m-hd {border-bottom-left-radius:10px;border-bottom-right-radius:10px;}
	.layout-bd .layout-collapsed-vert .layout-m-hd {border-bottom:0;}

	.layout-bd .layout-m-hd,
	.layout-bd .layout-m-bd,
	.layout-bd .layout-m-ft {padding:5px;}

	.layout-bd .layout-m-hd { height:25px; }

	.layout-bd .layout-m-hd p,
	.layout-bd .layout-m-ft p {margin:0}

	.layout-ft {background-color:#CCC;}
	.layout-ft p {font-size:80%;color:#666;margin:0;padding:0 0 10px 0;}

	.layout-bd .layout-collapsed-vert .layout-vert-expand-nub {display:inline;}

	.layout-bd .layout-collapsed-vert .layout-vert-expand-nub {display:inline;}
	.layout-bd .layout-collapsed-horiz .layout-left-expand-nub,
	.layout-bd .layout-collapsed-horiz .layout-right-expand-nub {cursor:pointer;background-color:#EEE;border-radius:10px;}
	.layout-bd .layout-collapsed-horiz .layout-horiz-expand-icon {padding:20px 2px 2px 2px;}

	#db-table-list-container { width:15em; }
	#db-table-list-content { margin-left:0; }
	.db-name { font-weight:bold; }
	#db-table-list .yui3-accordion-section ul { list-style-type:none; margin-left:1em; }

	#cmd-module { height:4em; border:0; }
	#cmd-module .layout-m-bd { height:100%; padding:0; overflow:hidden; }
	#cmd-input { height:95%; width:99%; }

	.layout-m-bd, #cmd-input { font-family:monospace; }

	#cmd-output p.error { font-weight:bold; color:red; }
	#cmd-output th, #cmd-output td { padding:2px; border:1px solid #C3C3C3; }
	#cmd-output th { border-bottom-color:#666; }
	#cmd-output td.null { color:#999; }

	.regular-title { padding-top:6px; }
	</style>
</head>

<body>

<div class="layout-hd">
	<h1>MySQL Console</h1>
</div>

<div class="layout-bd" style="visibility:hidden;">
	<div class="layout-module-col layout-not-managed">
		<div class="layout-module">
			<div class="layout-m-hd">
				<p>
					<button class="layout-left-collapse-nub">&larr;</button>
					<span class="collapsible-title">Databases</span>
				</p>
			</div>
			<div id="db-table-list-container" class="layout-m-bd">
				<div id="db-table-list"></div>
				<ul id="db-table-list-content" class="yui3-widget-loading">
					<?php
						foreach ($db_list as $name => $table_list)
						{
							echo '<li>';
							echo '<div class="db-name">'.htmlspecialchars($name).'</div>';
							echo '<div><ul>';
							foreach ($table_list as $table)
							{
								echo '<li>'.htmlspecialchars($table).'</li>';
							}
							echo '</ul></div>';
							echo '</li>';
						}
					?>
				</ul>
			</div>
			<div class="layout-left-expand-nub">
				<div class="layout-horiz-expand-icon">&rarr;</div>
			</div>
		</div>
	</div>
	<div class="layout-module-col">
		<div class="layout-module height:25%">
			<div class="layout-m-hd">
				<p class="regular-title">Command Output</p>
			</div>
			<div id="cmd-output" class="layout-m-bd"></div>
		</div>
		<div id="cmd-module" class="layout-module layout-not-managed">
			<div class="layout-m-bd">
				<textarea id="cmd-input"></textarea>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
YUI({
//	filter: 'raw', combine: false,
	gallery: 'gallery-2012.12.19-21-23'
}).use(
	'gallery-layout-cols', 'gallery-accordion-horiz-vert',
	'io-base', 'json',
	'event-key', 'gallery-scrollintoview',
	'gallery-funcprog', 'escape',
function(Y) {
"use strict";

var line_buffer_length = <?php echo json_encode($line_buffer_length); ?>,
	current_db         = null;

new Y.PageLayout();

Y.on('domready', function()
{
	var table_list = new Y.Accordion(
	{
		srcNode: '#db-table-list-content',
		allowAllClosed:          true,
		allowMultipleOpen:       true,
		replaceTitleContainer:   false,
		replaceSectionContainer: false
	});
	table_list.render('#db-table-list');

	Y.delegate('click', function(e)
	{
		var index = table_list.findSection(e.target);
		if (index >= 0)
		{
			table_list.toggleSection(index);
		}
	},
	'#db-table-list-container', '.db-name');

	Y.one('#cmd-input').on('key', sendCommand, 'enter');
});

function sendCommand(e)
{
	var cmd = e.target.get('value');

	var m = /use\s+([a-z0-9_]+)/i.exec(cmd);
	if (m && m.length)
	{
		current_db = m[1];
	}

	Y.io(<?php echo json_encode($_SERVER['PHP_SELF']); ?>,
	{
		method: 'POST',
		data:
		{
			db:  current_db,
			cmd: cmd
		},
		on:
		{
			success: function(id, o)
			{
				var data = Y.JSON.parse(o.responseText);
				if (data.error)
				{
					showResult(data.error, null, 'error');
				}
				else
				{
					showResult(cmd + ': OK', data, 'success');
				}
			},
			failure: function(id, o)
			{
				showResult('Server Error', 'error');
			}
		}
	});

	Y.later(0, null, function()
	{
		e.target.set('value', '');
	});
}

function showResult(msg, data, type)
{
	if (data && data.length)
	{
		var titles = Y.Object.keys(data[0]);

		var table = '<table><tr>'

		table += Y.reduce(titles, '', function(s1, title)
		{
			return s1 + '<th>' + Y.Escape.html(title) + '</th>';
		});

		table += '</tr>';

		table += Y.reduce(data, '', function(s1, row)
		{
			var s2 = Y.reduce(row, '', function(s3, value)
			{
				return s3 + Y.Lang.sub('<td{c}>{t}</td>',
				{
					c: value === null ? ' class="null"' : '',
					t: Y.Escape.html(value)
				});
			});
			return s1 + '<tr>' + s2 + '</tr>';
		});

		table += '</table>';
	}

	var output = Y.one('#cmd-output');

	output.append(Y.Lang.sub('<div><p class="{type}">{text}</p>{table}</div>',
	{
		type:  type,
		text:  Y.Escape.html(msg),
		table: table || ''
	}));

	while (output.get('children').length > line_buffer_length)
	{
		output.get('firstChild').remove(true);
	}

	output.get('lastChild').scrollIntoView();
}

});
</script>

</body>
</html>
