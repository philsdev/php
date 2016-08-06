$().ready( function() {
	$.ajaxSetup({ cache:false });
	
	$('#fetcher-go').click( function() {
		$('#results').html('').hide();
		$('#fetcher').hide();
		$('#waiter').show();
		$.get( 
			'/app/queue/',
			function(data) {
				$('#fetcher').show();
				$('#waiter').hide();
				$('#results').html('Done! Results: ' + data).show();
			}
		);
	});
	
	$('#queue').children('tbody').children('tr[qid]').children('td').children('a').click( function() {
		var item = {
			qid: $(this).parent('td').parent('tr').attr('qid'),
			id: $(this).text(),
			action: $(this).parent('td').parent('tr').children('td[class="action"]').text()
		};
		
		self.location.href = '/app/admin/read/?qid=' + item.qid + '&id=' + item.id + '&action=' + item.action;
	});
	
	$('#recent').children('tbody').children('tr[qid]').children('td').children('a').click( function() {
		var item = {
			qid: $(this).parent('td').parent('tr').attr('qid'),
			id: $(this).text(),
			action: $(this).parent('td').parent('tr').children('td[class="action"]').text()
		};
		
		self.location.href = '/app/admin/readonly/?qid=' + item.qid + '&id=' + item.id + '&action=' + item.action;
	});
	
	$('#failures').children('tbody').children('tr[qid]').children('td').children('a').click( function() {
		var item = {
			qid: $(this).parent('td').parent('tr').attr('qid'),
			id: $(this).text(),
			action: $(this).parent('td').parent('tr').children('td[class="action"]').text()
		};
		
		self.location.href = '/app/admin/update/?qid=' + item.qid + '&id=' + item.id + '&action=' + item.action;
	});
	
	$('#options').children('tbody').children('tr[vid]').children('td').children('a').click( function() {
		var item = {
			vid: $(this).parent('td').parent('tr').attr('vid')
		};
		
		self.location.href = '/app/admin/options/edit/?vid=' + item.vid;
	});
});