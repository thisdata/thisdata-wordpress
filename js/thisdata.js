
window.thisdata=window.thisdata||[],thisdata.load=function(a){if(!("ThisData"in window||window.thisdata.loading)){window.thisdata.loading=!0,window.thisdata.options=a;var b=document.createElement("script");b.src="https://api.thisdata.com/js/thisdata-LATEST.js",b.async=!0;var c=document.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}};

thisdata.load(ThisDataPlugin);
