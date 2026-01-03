async function initializeApp() {
  if (window.elementSdk) {
    window.elementSdk.init({
      defaultConfig,
      onConfigChange
    });
  }
}

initializeApp();
