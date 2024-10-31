<?xml version='1.0' encoding='ISO-8859-1'?>

<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform' xmlns:php="http://php.net/xsl" xsl:extension-element-prefixes="php" exclude-result-prefixes="php">
	<xsl:output method='html' />

	<xsl:param name='Itm'/>
	<xsl:param name='Now'/>
	<xsl:param name='cutoffDate'/>
	<xsl:param name='PageAdmin'/>
	<xsl:param name='uID'/>
	
	<xsl:variable name='lStyle' select='//items/@style'/>
	<xsl:variable name='ShowAsDate' select='php:functionString("scheduled_showasdate")'/>

	<xsl:variable name='v' select='*/item[string(@active)!=""][$lStyle="Ascending" or $lStyle="Descending" or ($Now > number(@from))][$lStyle="Ascending" or $lStyle="Descending" or (number(@to) > $Now)][$lStyle!="Itinerary" or $cutoffDate > number(@from)]'/>
	<xsl:variable name='n' select='count($v)'/>

	<xsl:template match='/'>
	<div id='{$uID}' style='display:inline;'>
		<xsl:if test='name(/*)!="items"'><xsl:copy-of disable-output-escaping="yes" select='.'/></xsl:if>
		<xsl:if test='name(/*)="items"'>

			<xsl:if test="false()">
				Itm=<xsl:value-of select='$Itm'/><br/>
				Now=<xsl:value-of select='$Now'/><br/>
				cutoffDate=<xsl:value-of select='$cutoffDate'/><br/>
				PageAdmin=<xsl:value-of select='$PageAdmin'/><br/>
				uID=<xsl:value-of select='$uID'/><br/>
				<hr/>
				lStyle=<xsl:value-of select='$lStyle' /><br/>
				ShowAsDate=<xsl:value-of select='$ShowAsDate' /><br/>
				n=<xsl:value-of select='$n'/><br/>
				c=<xsl:value-of select='count(*/item)'/><br/>
			</xsl:if>

			<xsl:choose>
				<xsl:when test='$lStyle="Scheduled" and $n=0'><xsl:for-each select='*/item[string(@default)!=""][1]'><xsl:call-template name='DisplayItem'/></xsl:for-each></xsl:when>
				<xsl:when test='$lStyle="Scheduled"'><xsl:for-each select='$v[1]'><xsl:call-template name='DisplayItem'/></xsl:for-each></xsl:when>
				<xsl:when test='$lStyle="Single"'><xsl:for-each select='*/item[number(@active)>0][1]'><xsl:call-template name='DisplayItem'/></xsl:for-each></xsl:when>
				<xsl:when test='$lStyle="Random"'>
					<xsl:variable name='x' select='php:functionString("scheduled_random_number", $n)' />
					<xsl:for-each select='$v[position()=$x]'>
						<xsl:call-template name='DisplayItem'/>
					</xsl:for-each>
				</xsl:when>
				<xsl:when test='$lStyle="Itinerary"'>
					<xsl:for-each select='*/item[number(@active)>0][number(@to) > $Now][$lStyle!="Itinerary" or $cutoffDate > number(@from)]'>
						<xsl:sort select='from' order='ascending'/>
						<xsl:call-template name='DisplayItem'/>
						<xsl:if test='position()!=last()'><hr/></xsl:if>
					</xsl:for-each>
				</xsl:when>
				<xsl:when test='$lStyle="Descending"'>
					<xsl:for-each select='$v'>
						<xsl:sort select='@from' order='descending'/>
						<xsl:call-template name='DisplayItem'/>
						<xsl:if test='position()!=last()'><br/><br/></xsl:if>
					</xsl:for-each>
				</xsl:when>
				<xsl:when test='$lStyle="Ascending"'>
					<xsl:for-each select='$v'>
						<xsl:sort select='@from' order='ascending'/>
						<xsl:call-template name='DisplayItem'/>
						<xsl:if test='position()!=last()'><br/><br/></xsl:if>
					</xsl:for-each>
				</xsl:when>

				<xsl:otherwise>
					<xsl:for-each select='$v'>
						<xsl:sort select='@from' order='descending'/>
						<xsl:call-template name='DisplayItem'/>
						<xsl:if test='position()!=last()'><br/><br/></xsl:if>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:if test='number($PageAdmin)>0'><xsl:call-template name='ItemAdmin'/></xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template name='DisplayItem'>
		<xsl:variable name='newcontent' select='php:functionString("scheduled_item_preprocess", .)'/>
		<xsl:choose>
			<xsl:when test='$lStyle="Itinerary"'>
				<xsl:value-of select='title'/><br/>
				Date: <xsl:value-of select='from'/> - <xsl:value-of select='to'/><br/>
				<xsl:value-of select='$newcontent' disable-output-escaping='yes'/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of disable-output-escaping='yes' select='$newcontent'/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="ItemAdmin">
		<xsl:call-template name='ItemAdminMain'/>
		<xsl:call-template name='ItemAdminOptions'/>
		<xsl:call-template name='ItemAdminEdit'/>
<span id='{$uID}_open' style='display:inline;'>
		<br />
		<button onclick='return Scheduled_Admin("main","{$uID}","{$Itm}")' class='button-primary'>Edit</button>
</span>
	</xsl:template>

	<xsl:template name='ItemAdminMain'>
<span id='{$uID}_main' style='display:none;' class='scheduled_admin scheduled_admin_main'>
<div id='{$uID}_main_loader' class='scheduled_loader' style='display:none;'></div>
<table class='Scheduler' cellpadding='15' cellspacing='0'>
	<tr class='scheduled_showasdate'>
		<td colspan='3' align='center'>
			<form id='ScheduledShowAs_{$uID}' style='display:block'>
			Display As: <input name='ShowAsDate' value='{$ShowAsDate}' size='10'/>
			<button onClick='javascript:var asad=SetQueryStringValue("-", "ShowAsDate", document.ScheduledShowAs_{$uID}.ShowAsDate_{$uID}.value);window.location=asad;return false;'>Update</button>
			</form>
		</td>
	</tr>
	<tr>
		<td><form id='ScheduledAdminForm_{$uID}' name='ScheduledAdminForm_{$uID}'><xsl:value-of disable-output-escaping='yes' select='php:functionString("scheduled_lookuplist",$Itm)' /></form><span style='font: 8px/0px tahoma'>* = default, X = inactive, _ = Normal</span></td>
		<td>
			<button onClick='javascript:Scheduled_Admin("edit","{$uID}","{$Itm}");return false;'>Edit</button><br/>
			<br/>
			<button onClick='javascript:Scheduled_Admin("delete","{$uID}","{$Itm}");return false;'>Delete</button><br/>
			<br/>
			<button onClick='javascript:Scheduled_Admin("create","{$uID}","{$Itm}");return false;'>Create</button><br/>
			<br/>
			<button onClick='javascript:Scheduled_Admin("options","{$uID}","{$Itm}");return false;'>Options</button><br/>
			<br/>
			<button onClick='javascript:Scheduled_Admin("close","{$uID}","{$Itm}");return false;'>Close</button><br/>
		</td>
	</tr>
</table>
</span>
	</xsl:template>

	<xsl:template name='ItemAdminOptions'>
<div id='{$uID}_options' style='display:none;' class='scheduled_admin scheduled_admin_options'>
<div id='{$uID}_options_loader' class='scheduled_loader' style='display:none;'></div>
<form id="ScheduledOptionForm_{$uID}" name="ScheduledOptionForm_{$uID}" action='#'>
<table class='scheduled'>
<tr><td>Name:</td><td><input name='name' /></td></tr>
<tr><td>Style:</td><td><xsl:value-of disable-output-escaping='yes' select='php:functionString("scheduled_styleselect", "style")'/></td></tr>
<tr><td>XSL:</td><td><input name='xsl' /></td></tr>
<tr><td>Access:</td><td><xsl:value-of disable-output-escaping='yes' select='php:functionString("scheduled_role_select","access")' /></td></tr>
<tr>
	<td colspan='2' align='center'>
		<button onClick='Scheduled_SaveOptions("{$uID}", "{$Itm}");return Scheduled_Admin("main", "{$uID}", "{$Itm}");'>Save</button>
		<button onClick='return Scheduled_Admin("main", "{$uID}", "{$Itm}");'>Cancel</button>
	</td>
</tr>
</table>
</form>
</div>
	</xsl:template>

	<xsl:template name='ItemAdminEdit'>
<div id='{$uID}_edit' style='display:none;' class='scheduled_admin scheduled_admin_edit'>
<div id='{$uID}_edit_loader' class='scheduled_loader' style='display:none;'></div>
<form id="ScheduledEditForm_{$uID}" name="ScheduledEditForm_{$uID}" action='#'>
<input name='ix' type='hidden' /> 
<table class='scheduled'>
	<tr><td align='center'>
		Title: <input name='itemtitle' /><br/>
		<input name='from' /> To <input name='to' /><br/>
		<textarea class='scheduled' name='content' rows='10' cols='50'></textarea><br/>
		Default? <input type='checkbox' name='default'/> Active? <input type='checkbox' name='active' /><br/>
<br/>
<button type='submit' onClick='return Scheduled_SaveItem("{$uID}", "{$Itm}");'>Save</button>
<button type='cancel' onClick='return Scheduled_Admin("main", "{$uID}", "{$Itm}");'>Cancel</button>
<br/>
	</td></tr>
</table>
</form>
</div>
	</xsl:template>
</xsl:stylesheet>