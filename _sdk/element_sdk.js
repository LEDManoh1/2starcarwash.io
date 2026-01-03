// Minimal element_sdk stub to avoid file-not-found errors during local dev
(function(){
  if (window.elementSdk) return;
  window.elementSdk = {
    config: null,
    init: function(opts){
      this.config = opts && opts.defaultConfig ? opts.defaultConfig : {};
      if (opts && typeof opts.onConfigChange === 'function') {
        // call once to apply defaults
        opts.onConfigChange(this.config);
      }
      console.info('[element_sdk stub] initialized');
    },
    setConfig: function(cfg){ this.config = Object.assign({}, this.config, cfg); console.info('[element_sdk stub] setConfig', cfg); }
  };
})();