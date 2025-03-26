// home.js
const profileDetails = document.getElementById("profileDetails");
const leftPanel = document.getElementById("leftPanel");
const editProfile = document.getElementById("editBio");
const editbtn = document.getElementById("editProfile");
const closebtn = document.getElementById("close");

function toggleMenu() {
  leftPanel.classList.toggle("active");
}

function toggleProfile() {
  if (
    profileDetails.style.display === "none" ||
    profileDetails.style.display === ""
  ) {
    profileDetails.style.display = "block"; // Make it visible
    setTimeout(() => {
      profileDetails.classList.add("active"); // Trigger animation
    }, 10);
  } else {
    profileDetails.classList.remove("active"); // Hide with animation
    setTimeout(() => {
      profileDetails.style.display = "none"; // Hide completely after animation
    }, 500); // Matches CSS transition duration
  }
}

const resultsContainer = document.getElementById("searchResults");
function searchUsers() {
  let query = document.getElementById("searchInput").value.trim();

  if (query === "") {
    resultsContainer.innerHTML = "";
    return;
  }

  // Validate numeric input
  if (!/^\d+$/.test(query)) {
    resultsContainer.innerHTML =
      "<p class='error'>Please enter a valid numeric User ID</p>";
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "search.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        resultsContainer.innerHTML = xhr.responseText;
      } else {
        resultsContainer.innerHTML =
          "<p class='error'>Error fetching results</p>";
      }
    }
  };

  xhr.send("query=" + encodeURIComponent(query));
}
editbtn.addEventListener("click", () => {
  editProfile.style.display = "block";
  profileDetails.style.display = "none";
});

closebtn.addEventListener("click", () => {
  editProfile.style.display = "none";
});

document.addEventListener("click", function (e) {
  if (e.target && e.target.classList.contains("msgbtn")) {
    const targetUserId = e.target.getAttribute("data-userid");

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "send_request.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
      const response = JSON.parse(this.responseText);
      if (response.status === "success") {
        alert("Friend request sent!");
      } else {
        alert("Error: " + response.message);
      }
    };

    xhr.send("target_user_id=" + encodeURIComponent(targetUserId));
  }
});
document.getElementById("fileInput").addEventListener("change", function (e) {
  const fileInfo = document.getElementById("fileInfo");
  const wrapper = document.querySelector(".attachment-wrapper");
  if (this.files.length > 0) {
    fileInfo.textContent = this.files[0].name;
    wrapper.classList.add("has-file");
  } else {
    wrapper.classList.remove("has-file");
  }
});

document.querySelector(".attach-btn").addEventListener("click", function () {
  document.getElementById("fileInput").click();
});
document
  .getElementById("analysisForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    clearWarnings();
    showLoading();

    const formData = new FormData(e.target);
    const postContainer = document.querySelector(".post-container");
    postContainer.innerHTML = ""; // Clear previous content

    try {
      const response = await fetch("api.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      processAnalysisResults(data, formData);
    } catch (error) {
      console.error("Error:", error);
      showWarning(`Error: ${error.message}`);
    } finally {
      hideLoading();
    }
  });

function processAnalysisResults(data, formData) {
  let hasWarnings = false;

  // Image analysis warnings
  if (data.image_analysis?.responses?.[0]?.safeSearchAnnotation) {
    const safeSearch = data.image_analysis.responses[0].safeSearchAnnotation;
    if (checkSafetyLevel(safeSearch, "adult", "Adult content (Image)"))
      hasWarnings = true;
    if (checkSafetyLevel(safeSearch, "violence", "Violent content (Image)"))
      hasWarnings = true;
    if (checkSafetyLevel(safeSearch, "racy", "Racy content (Image)"))
      hasWarnings = true;
    if (checkSafetyLevel(safeSearch, "medical", "Medical content (Image)"))
      hasWarnings = true;
    if (checkSafetyLevel(safeSearch, "spoof", "Spoofed content (Image)"))
      hasWarnings = true;
  }

  // Video analysis warnings
  if (data.video_analysis?.annotationResults?.[0]?.safeSearchAnnotations) {
    const videoSafeSearch =
      data.video_analysis.annotationResults[0].safeSearchAnnotations;
    for (let frame of videoSafeSearch) {
      if (checkSafetyLevel(frame, "adult", "Adult content (Video)"))
        hasWarnings = true;
      if (checkSafetyLevel(frame, "violence", "Violent content (Video)"))
        hasWarnings = true;
      if (checkSafetyLevel(frame, "racy", "Racy content (Video)"))
        hasWarnings = true;
      if (checkSafetyLevel(frame, "medical", "Medical content (Video)"))
        hasWarnings = true;
      if (checkSafetyLevel(frame, "spoof", "Spoofed content (Video)"))
        hasWarnings = true;
    }
  }

  // Text analysis warnings
  if (data.text_analysis?.documentSentiment) {
    const sentiment = data.text_analysis.documentSentiment;
    if (sentiment.score < -0.5) {
      showWarning("Warning: Highly negative text detected!");
      hasWarnings = true;
    }
  }

  // If no warnings, display uploaded files
  if (!hasWarnings) {
    displayFiles(formData);
    
    // Get the text content from the form
    const messageContent = document.querySelector('#analysisForm textarea').value;

    // Send via AJAX
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "send_message.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                // Clear textarea after successful send
                document.querySelector('#analysisForm textarea').value = '';
                // Optional: Add visual feedback
                alert('Message sent successfully!');
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('Error: ' + xhr.statusText);
        }
    };

    xhr.onerror = function() {
        alert('Request failed');
    };

    // Send the message content as URL-encoded parameter
    xhr.send("text=" + encodeURIComponent(messageContent));
}
}

function checkSafetyLevel(safeSearch, category, label) {
  const levels = ["LIKELY", "VERY_LIKELY"];
  if (levels.includes(safeSearch[category])) {
    showWarning(`Warning: Potential ${label} detected!`);
    return true; // Warning detected
  }
  return false; // No warning
}

function showWarning(message) {
  warningContainer.style.display = "block";
  const warning = document.createElement("div");
  warning.className = "warning-message";
  warning.innerHTML = `⚠️ ${message}`;
  warningContainer.appendChild(warning);

  setTimeout(() => {
    warning.remove();
    if (!warningContainer.hasChildNodes()) {
      warningContainer.style.display = "none";
    }
  }, 5000);
}

function clearWarnings() {
  warningContainer.innerHTML = "";
  warningContainer.style.display = "none";
}

function showLoading() {
  document.querySelector("button").innerHTML =
    '<div class="loader"></div> Analyzing...';
}

function hideLoading() {
  document.querySelector("button").innerHTML = "Check";
}

function displayFiles(formData) {
  const postContainer = document.querySelector(".post-container");
  postContainer.innerHTML = ""; // Clear previous content

  // Display text if entered
  const text = formData.get("text");
  if (text) {
    const textDiv = document.createElement("div");
    textDiv.className = "text-preview";
    textDiv.textContent = `📜 Text: ${text}`;
    postContainer.appendChild(textDiv);
  }

  // Display file if uploaded
  const file = formData.get("file");
  if (file && file.name) {
    const fileType = file.type.split("/")[0];

    const fileDiv = document.createElement("div");
    fileDiv.className = "file-preview";

    if (fileType === "image") {
      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      img.alt = "Uploaded Image";
      img.style.maxWidth = "300px";
      fileDiv.appendChild(img);
    } else if (fileType === "video") {
      const video = document.createElement("video");
      video.src = URL.createObjectURL(file);
      video.controls = true;
      video.style.maxWidth = "300px";
      fileDiv.appendChild(video);
    } else {
      fileDiv.textContent = `📄 File Uploaded: ${file.name}`;
    }

    postContainer.appendChild(fileDiv);
  }
}

document.getElementById("fileInput").addEventListener("change", function (e) {
  const fileInfo = document.getElementById("fileInfo");
  if (this.files && this.files.length > 0) {
    fileInfo.textContent = this.files[0].name;
    fileInfo.style.display = "inline";
  } else {
    fileInfo.style.display = "none";
  }
});



