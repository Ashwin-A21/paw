document.addEventListener("DOMContentLoaded", () => {
  // ---- State ----
  let mouseX = 0;
  let mouseY = 0;
  let isTyping = false;
  let isLookingAtEachOther = false;
  let showPassword = false;

  // elements
  const emailInput = document.getElementById("email");
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");
  const togglePassBtn = document.getElementById("toggle-password");
  const toggleIcon = togglePassBtn.querySelector("i"); // lucide icon

  const purple = document.getElementById("char-purple");
  const black = document.getElementById("char-black");
  const orange = document.getElementById("char-orange");
  const yellow = document.getElementById("char-yellow");

  const allCharacters = [
    { el: purple, name: "purple", hasFaceWrap: true }, // Face translation applies to eyes container
    { el: black, name: "black", hasFaceWrap: true },
    { el: orange, name: "orange", hasFaceWrap: false }, // Eyes move independently
    { el: yellow, name: "yellow", hasFaceWrap: false, hasMouth: true },
  ];

  // ---- Event Listeners ----
  window.addEventListener("mousemove", (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!isLookingAtEachOther && !isPeeking()) {
      updateCharacters();
    }
  });

  // Typing Logic
  const handleTypingStart = () => {
    isTyping = true;
    // Look at each other for 800ms when typing starts (per React code logic)
    isLookingAtEachOther = true;
    updateLookAtEachOther(); // Immediate update

    // Reset look at each other after 800ms
    clearTimeout(window.typingTimer);
    window.typingTimer = setTimeout(() => {
      isLookingAtEachOther = false;
      // Back to tracking
      updateCharacters();
    }, 800);
  };

  const handleTypingEnd = () => {
    isTyping = false;
  };

  emailInput.addEventListener("focus", handleTypingStart);
  emailInput.addEventListener("blur", handleTypingEnd);
  emailInput.addEventListener("input", () => {
    // Keeps the "isLookingAtEachOther" active if typing continues rapidly?
    // The React code says: useEffect on isTyping change.
    // Here we just trigger the start logic again if not already active?
    // Actually react logic: useEffect([isTyping]) -> sets flag true, timeout sets false.
    // So simple focus is enough to trigger the "Look at each other" moment.
  });

  if(usernameInput) {
      usernameInput.addEventListener('focus', handleTypingStart);
      usernameInput.addEventListener('blur', handleTypingEnd);
  }

  passwordInput.addEventListener("focus", handleTypingStart);
  passwordInput.addEventListener("blur", handleTypingEnd);

  // Password visibility
  togglePassBtn.addEventListener("click", () => {
    showPassword = !showPassword;
    passwordInput.type = showPassword ? "text" : "password";

    // Update Icon
    // We need to re-render lucide icon or swap classes.
    // Lucide renders SVG. Simple way:
    togglePassBtn.innerHTML = showPassword
      ? '<i data-lucide="eye-off"></i>'
      : '<i data-lucide="eye"></i>';
    lucide.createIcons();

    // Trigger update
    updateCharacters();
  });

  // Peeking Logic (Purple Character)
  // "Purple sneaky peeking animation when typing password and it's visible"
  // React: useEffect checks [password, showPassword].
  // If pass.length > 0 && showPassword, schedule peek.

  passwordInput.addEventListener("input", () => {
    if (passwordInput.value.length > 0 && showPassword) {
      triggerPeek();
    }
  });

  function isPeeking() {
    // Return active peek state if needed
    return document.body.classList.contains("is-peeking");
  }

  function triggerPeek() {
    if (isPeeking()) return;

    // Only peek randomly or consistently? React code: random interval.
    // For simplicity: Peek immediately on type if visible.
    document.body.classList.add("is-peeking");

    // Purple peeks: moves eyes to look at password field
    updateCharacters(true);

    setTimeout(() => {
      document.body.classList.remove("is-peeking");
      updateCharacters();
    }, 800);
  }

  // ---- Animation Logic ----

  function updateCharacters(forcePeek = false) {
    allCharacters.forEach((char) => {
      const rect = char.el.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const centerY = rect.top + rect.height / 3;

      // 1. Calculate Body Skew/Lean
      const deltaX = mouseX - centerX;
      const skewX = Math.max(-6, Math.min(6, -deltaX / 120)); // React: -deltaX / 120

      // Apply Skew
      // React: `skewX(${purplePos.bodySkew}deg)` or `translateX`
      // Purple specific: if typing/pass visible: `skewX(${(skew) - 12}deg) translateX(40px)`

      let transform = `skewX(${skewX}deg)`;

      // Specific Logic for states
      if (char.name === "purple") {
        if (passwordInput.value.length > 0 && showPassword) {
          transform = `skewX(0deg)`; // Rigid when peeking
        } else if (
          isTyping ||
          (passwordInput.value.length > 0 && !showPassword)
        ) {
          // Hiding behind black/leaning away
          transform = `skewX(${skewX - 12}deg) translateX(40px)`;
          // Increase height
          char.el.style.height = "440px";
        } else {
          char.el.style.height = "400px";
        }
      }
      if (char.name === "black") {
        if (passwordInput.value.length > 0 && showPassword) {
          transform = `skewX(0deg)`;
        } else if (isLookingAtEachOther) {
          transform = `skewX(${skewX * 1.5 + 10}deg) translateX(20px)`;
        } else if (isTyping) {
          transform = `skewX(${skewX * 1.5}deg)`;
        }
      }

      char.el.style.transform = transform;

      // 2. Calculate Face/Eye Position
      // Face moves slightly towards mouse
      const faceX = Math.max(-15, Math.min(15, deltaX / 20));
      const faceY = Math.max(-10, Math.min(10, (mouseY - centerY) / 30));

      const eyesContainer = char.el.querySelector(".eyes-container");

      // Base Positions from CSS (approximate defaults to add delta to)
      let baseLeft = 0,
        baseTop = 0;
      if (char.name === "purple") {
        baseLeft = 45;
        baseTop = 40;
      }
      if (char.name === "black") {
        baseLeft = 26;
        baseTop = 32;
      }
      if (char.name === "orange") {
        baseLeft = 82;
        baseTop = 90;
      }
      if (char.name === "yellow") {
        baseLeft = 52;
        baseTop = 40;
      }

      // Apply Face Move
      if (char.hasFaceWrap) {
        // If peeking/typing logic doesn't override
        if (passwordInput.value.length > 0 && showPassword) {
          // Locked face
          eyesContainer.style.left = (char.name === "purple" ? 20 : 10) + "px";
          eyesContainer.style.top = (char.name === "purple" ? 35 : 28) + "px";
        } else if (isLookingAtEachOther) {
          // Look at neighbor
          eyesContainer.style.left = (char.name === "purple" ? 55 : 32) + "px";
          eyesContainer.style.top = (char.name === "purple" ? 65 : 12) + "px";
        } else {
          eyesContainer.style.left = baseLeft + faceX + "px";
          eyesContainer.style.top = baseTop + faceY + "px";
        }
      } else {
        // Orange/Yellow face movement applied to eyes/mouth individually?
        // React code applies faceX/Y to eye container left/top for Orange/Yellow too.
        if (passwordInput.value.length > 0 && showPassword) {
          eyesContainer.style.left = (char.name === "orange" ? 50 : 20) + "px";
          eyesContainer.style.top = (char.name === "orange" ? 85 : 35) + "px";
        } else {
          eyesContainer.style.left = baseLeft + faceX + "px";
          eyesContainer.style.top = baseTop + faceY + "px";
        }

        // Mouth for yellow
        if (char.hasMouth) {
          const mouth = char.el.querySelector(".mouth");
          if (passwordInput.value.length > 0 && showPassword) {
            mouth.style.left = "10px";
            mouth.style.top = "88px";
          } else {
            mouth.style.left = 40 + faceX + "px";
            mouth.style.top = 88 + faceY + "px";
          }
        }
      }

      // 3. Pupil Tracking
      const pupils = char.el.querySelectorAll(".pupil, .pupil-only");
      pupils.forEach((pupil) => {
        // Determine target to look at
        let targetX = mouseX;
        let targetY = mouseY;

        // Overrides
        if (passwordInput.value.length > 0 && showPassword) {
          // Look at password field (approx coords??) OR Fixed offset
          // React code: forceLookX/Y.
          // We can simulate force look by setting delta manually or hardcoding transform
          // Let's hardcode transform for "Force Look"

          // Values from React logic:
          // Purple Peeking: x:4, y:5
          // Others: x:-4 or -5

          if (char.name === "purple") {
            // If peeking vs not peeking, but we are in showPassword state
            // The 'forcePeek' arg is passed if we just typed.
            // But strictly: showPassword + active typing often triggers peek.
            // Let's assume standard "Watch the field" look:
            pupil.style.transform = `translate(4px, 5px)`;
          } else {
            pupil.style.transform = `translate(-4px, 4px)`;
          }
          return;
        }

        if (isLookingAtEachOther) {
          // Look at each other
          if (char.name === "purple")
            pupil.style.transform = `translate(3px, 4px)`;
          else if (char.name === "black")
            pupil.style.transform = `translate(0px, -4px)`;
          else pupil.style.transform = `translate(0,0)`;
          return;
        }

        // Standard Mouse Tracking
        // Get pupil absolute center
        const pRect = pupil.getBoundingClientRect();
        const pCenterX = pRect.left + pRect.width / 2;
        const pCenterY = pRect.top + pRect.height / 2;

        const pDeltaX = targetX - pCenterX;
        const pDeltaY = targetY - pCenterY;

        const maxDist = char.name === "purple" ? 5 : 4;
        const distance = Math.min(
          Math.sqrt(pDeltaX ** 2 + pDeltaY ** 2),
          maxDist,
        );
        const angle = Math.atan2(pDeltaY, pDeltaX);

        const trX = Math.cos(angle) * distance;
        const trY = Math.sin(angle) * distance;

        pupil.style.transform = `translate(${trX}px, ${trY}px)`;
      });
    });
  }

  function updateLookAtEachOther() {
    // Force update with specific flags
    updateCharacters();
  }

  // Initial Tick
  updateCharacters();
});
