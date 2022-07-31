function fixSharpUrl() {
	url = document.location.href;
	tmp = url.split('/');
	s = tmp.pop();
	cnt = tmp.length-1;
	ss = '';
	if(str.match('/^#/')) {
		for(z=0; z<cnt; z++) {
			ss += tmp[z];
		}
                alert(ss);
		location.href = ss;
	}
}
