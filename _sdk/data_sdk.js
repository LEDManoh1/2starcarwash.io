// Minimal data_sdk stub for local dev
(function(){
  if (window.dataSdk) return;
  window.dataSdk = {
    get: function(){ return null; },
    set: function(){ return null; },
    init: async function(handler){
      // emulate async init and call initial onDataChanged if provided
      if (handler && typeof handler.onDataChanged === 'function') {
        try { handler.onDataChanged([]); } catch(e){}
      }
      return { isOk: true };
    }
  };
})();