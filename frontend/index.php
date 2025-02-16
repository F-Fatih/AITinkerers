<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Research Paper Graph Visualization Tool</title>
  <!-- D3.js from CDN -->
  <script src="https://d3js.org/d3.v7.min.js"></script>

  <style>
    /********************************************************
     * GLOBAL & LAYOUT
     ********************************************************/
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      font-family: "Helvetica Neue", Arial, sans-serif;
      font-size: 16px;
      background-color: #f5f5f5;
      color: #333;
      /* Ensure no global scrollbars */
      overflow: hidden;
    }

    .container {
      display: flex;
      /* Fill entire viewport */
      height: 100vh;
      width: 100vw;
    }

    /********************************************************
     * LEFT PANEL: CHAT
     ********************************************************/
    #chat-panel {
      width: 20%;
      min-width: 220px;
      background-color: #fff;
      border-right: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      padding: 0.5rem;
      /* No vertical scroll by default on the panel itself */
      overflow: hidden;
    }

    #chat-panel h2 {
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }

    /* Only the messages area should scroll if needed */
    .chat-messages {
      flex-grow: 1;
      overflow-y: auto; 
      margin-bottom: 0.5rem;
      padding-right: 0.5rem;

      display: flex;
      flex-direction: column;
    }

    .chat-message {
      max-width: 70%;
      line-height: 1.4;
      padding: 0.4rem 0.6rem;
      border-radius: 6px;
      word-wrap: break-word;
      margin: 0.5rem 0;
    }
    .chat-message.bot {
      align-self: flex-start;
      background-color: #e8e8e8;
      color: #333;
    }
    .chat-message.user {
      align-self: flex-end;
      background-color: #007BFF;
      color: #fff;
      text-align: right;
    }

    .chat-input {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 0.5rem;
    }
    .chat-input input {
      flex-grow: 1;
      padding: 0.4rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .chat-input button {
      background-color: #007BFF;
      border: none;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
    .chat-input button:hover {
      background-color: #0056b3;
    }

    /********************************************************
     * CENTER PANEL: GRAPH
     ********************************************************/
    #graph-panel {
      flex-grow: 1;
      position: relative;
      /* Hide overflow so no scrollbar in the main area */
      overflow: hidden;
    }
    #graph {
      width: 100%;
      height: 100%;
    }

    /* Top overlay for color-mode selection */
    #color-mode-panel {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: rgba(255,255,255,0.8);
      border-radius: 6px;
      padding: 0.5rem;
      z-index: 99;
    }
    #color-mode-panel label {
      margin-right: 1rem;
      cursor: pointer;
    }
    #color-mode-panel input {
      margin-right: 0.3rem;
    }

    /********************************************************
     * TOOLTIP
     ********************************************************/
    /* Position in <body> so it can float freely over side panels */
    .tooltip {
      position: absolute;
      pointer-events: none;
      background: rgba(0,0,0,0.8);
      color: #fff;
      padding: 0.4rem 0.6rem;
      border-radius: 4px;
      font-size: 0.9rem;
      display: none;
      z-index: 9999;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      max-width: 250px; 
      word-wrap: break-word;
    }

    /********************************************************
     * RIGHT PANEL: DETAILS
     ********************************************************/
    #details-panel {
      width: 25%;
      min-width: 300px;
      max-width: 400px;
      background-color: #fff;
      border-left: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      /* Only this panel scrolls if content overflows */
      overflow-y: auto;
      padding: 1rem;
      transition: transform 0.4s ease, opacity 0.4s ease;
      opacity: 1;
    }
    #details-panel.hidden {
      transform: translateX(100%);
      opacity: 0;
    }

    /* Close button with '×' icon */
    .close-button {
      align-self: flex-end;
      background-color: transparent;
      color: #888;
      font-size: 1.5rem;
      border: none;
      outline: none;
      cursor: pointer;
      margin-bottom: 0.5rem;
      transition: color 0.2s ease;
    }
    .close-button:hover {
      color: #007BFF;
    }

    #details-panel h2 {
      margin-bottom: 0.5rem;
      font-size: 1.25rem;
    }
    #details-panel p {
      margin-bottom: 0.5rem;
      line-height: 1.5;
    }
    .details-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
      flex-wrap: wrap;
    }
    .details-actions button,
    .details-actions a {
      padding: 0.4rem 0.8rem;
      background-color: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }
    .details-actions button:hover,
    .details-actions a:hover {
      background-color: #0056b3;
    }

    @media (max-width: 800px) {
      #chat-panel { width: 25%; }
      #details-panel { width: 30%; }
    }
    @media (max-width: 600px) {
      #chat-panel { width: 30%; }
      #details-panel { width: 35%; }
    }

  </style>
</head>
<body>

  <div class="container">
    <!-- LEFT: CHAT -->
    <div id="chat-panel">
      <h2>Chat</h2>
      <div class="chat-messages" id="chat-messages"></div>
      <div class="chat-input">
        <input type="text" id="chat-input-field" placeholder="Ask about papers..." />
        <!-- Simple arrow icon -->
        <button id="chat-send-btn" aria-label="Send">➤</button>
      </div>
    </div>

    <!-- CENTER: GRAPH -->
    <div id="graph-panel">
      <!-- Color Mode Selection -->
      <div id="color-mode-panel">
        <label>
          <input type="radio" name="colorMode" value="subject" checked />
          Subject Grouping
        </label>
        <label>
          <input type="radio" name="colorMode" value="date" />
          Publication Date Fade
        </label>
      </div>
      <svg id="graph"></svg>
    </div>

    <!-- RIGHT: DETAILS -->
    <div id="details-panel" class="hidden">
      <button class="close-button" id="details-close-btn" aria-label="Close">×</button>
      <h2 id="paper-title">Paper Title</h2>
      <p><strong>Authors:</strong> <span id="paper-authors"></span></p>
      <p><strong>Subject:</strong> <span id="paper-category"></span></p>
      <p><strong>Publication Date:</strong> <span id="paper-date"></span></p>
      <p><strong>Abstract:</strong></p>
      <p id="paper-abstract"></p>
      <div class="details-actions">
        <a href="#" target="_blank" id="paper-arxiv-link">arXiv Link</a>
        <button id="paper-summary-btn">AI Summary</button>
        <button id="paper-new-search-btn">Start New Search</button>
      </div>
    </div>
  </div>

  <!-- Tooltip placed outside the graph panel so it can overlap both sides -->
  <div class="tooltip" id="graph-tooltip"></div>

  <script>
    /********************************************************
     * MOCK DATA
     ********************************************************/
    const papersData = [
      {
        id: 1,
        title: "Detecting Harmful Speech on Social Media",
        authors: ["Doe, J.", "Smith, A."],
        date: "2022-01-15",
        abstract: "This paper explores various machine learning approaches to detect harmful speech on social media. We evaluate multiple supervised and semi-supervised techniques and compare them on diverse datasets.",
        category: "Harmful Speech",
        arxivId: "2201.12345"
      },
      {
        id: 2,
        title: "Advanced NLP Techniques",
        authors: ["Johnson, R.", "Lee, S."],
        date: "2021-11-08",
        abstract: "We introduce new transformer architectures that push the state-of-the-art in NLP tasks, covering language modeling, QA, and text generation.",
        category: "NLP",
        arxivId: "2111.67890"
      },
      {
        id: 3,
        title: "Graph Neural Networks for Text Classification",
        authors: ["Williams, T.", "Zhang, M."],
        date: "2020-06-25",
        abstract: "This paper proposes a novel graph neural network model for text classification tasks, leveraging document-word adjacency and advanced pooling strategies.",
        category: "Graph Analysis",
        arxivId: "2006.11111"
      },
      {
        id: 4,
        title: "Multimodal Hate Speech Detection",
        authors: ["Kim, Y.", "Singh, P."],
        date: "2023-03-01",
        abstract: "Leveraging text and images, our system identifies hateful content more accurately by combining convolutional and transformer-based features.",
        category: "Harmful Speech",
        arxivId: "2303.99999"
      },
      {
        id: 5,
        title: "Ethical Implications of Large Language Models",
        authors: ["Anderson, K.", "Morales, E."],
        date: "2022-09-10",
        abstract: "We discuss the ethical considerations arising from the widespread use of large language models, focusing on bias, misinformation, and accountability.",
        category: "Ethics",
        arxivId: "2209.55555"
      },
      {
        id: 6,
        title: "Semi-Supervised Learning for Hate Speech Detection",
        authors: ["Taylor, B.", "Chen, D."],
        date: "2022-12-05",
        abstract: "This study highlights the benefits of semi-supervised approaches for detecting hate speech with limited labeled data and abundant unlabeled content.",
        category: "Harmful Speech",
        arxivId: "2212.44444"
      }
    ];

    const linksData = [
      { source: 1, target: 2 },
      { source: 1, target: 3 },
      { source: 1, target: 4 },
      { source: 1, target: 5 },
      { source: 1, target: 6 },
      { source: 2, target: 3 },
      { source: 4, target: 6 },
      { source: 3, target: 6 },
    ];

    /********************************************************
     * D3 FORCE GRAPH
     ********************************************************/
    const svg = d3.select("#graph");
    const width = document.getElementById("graph-panel").clientWidth;
    const height = document.getElementById("graph-panel").clientHeight;

    // Parse date for color scale
    const parseDate = d3.timeParse("%Y-%m-%d");
    papersData.forEach(d => {
      d.dateObj = parseDate(d.date);
    });

    // Subject-based color scale
    const categories = [...new Set(papersData.map(d => d.category))];
    const subjectColorScale = d3.scaleOrdinal()
      .domain(categories)
      .range(["#007BFF", "#28a745", "#fd7e14", "#6f42c1", "#20c997", "#ffc107"]);

    // Date-based color scale
    const minDate = d3.min(papersData, d => d.dateObj);
    const maxDate = d3.max(papersData, d => d.dateObj);
    const dateColorScale = d3.scaleTime()
      .domain([minDate, maxDate])
      .range(["#cfe2ff", "#002855"]);

    let currentColorMode = "subject";

    function getNodeColor(d) {
      return currentColorMode === "subject"
        ? subjectColorScale(d.category)
        : dateColorScale(d.dateObj);
    }

    // Force simulation with collision
    const simulation = d3.forceSimulation(papersData)
      .force("link", d3.forceLink(linksData).id(d => d.id).distance(180))
      .force("charge", d3.forceManyBody().strength(-300))
      .force("center", d3.forceCenter(width / 2, height / 2))
      .force("collision", d3.forceCollide().radius(75).strength(1))
      .alphaDecay(0.02);

    // Links
    const linkGroup = svg.append("g").attr("class", "links");
    const link = linkGroup
      .selectAll("line")
      .data(linksData)
      .enter()
      .append("line")
      .attr("stroke", "#999")
      .attr("stroke-opacity", 0.6)
      .attr("stroke-width", 2);

    // Nodes as (g) containing a rect + text
    const nodeGroup = svg.append("g").attr("class", "nodes");
    const node = nodeGroup
      .selectAll("g")
      .data(papersData)
      .enter()
      .append("g")
      .on("mouseover", handleMouseOver)
      .on("mousemove", handleMouseMove)
      .on("mouseout", handleMouseOut)
      .on("click", handleNodeClick);

    // Rounded rectangle
    node.append("rect")
      .attr("rx", 10)
      .attr("ry", 10)
      .attr("width", 140)
      .attr("height", 40)
      .attr("x", -70) 
      .attr("y", -20)
      .attr("fill", d => getNodeColor(d));

    // Title text
    node.append("text")
      .attr("text-anchor", "middle")
      .attr("dominant-baseline", "middle")
      .attr("fill", "#fff")
      .style("font-size", "12px")
      .text(d => {
        const maxLen = 20;
        return d.title.length > maxLen
          ? d.title.slice(0, maxLen) + "…"
          : d.title;
      });

    // Pin node #1 to center
    simulation.on("tick", () => {
      link
        .attr("x1", d => d.source.x)
        .attr("y1", d => d.source.y)
        .attr("x2", d => d.target.x)
        .attr("y2", d => d.target.y);

      node.attr("transform", d => `translate(${d.x},${d.y})`);

      const centerNode = papersData.find(d => d.id === 1);
      if (centerNode) {
        centerNode.fx = width / 2;
        centerNode.fy = height / 2;
      }
    });

    // Zoom/pan
    const zoom = d3.zoom()
      .scaleExtent([0.5, 5])
      .on("zoom", event => {
        linkGroup.attr("transform", event.transform);
        nodeGroup.attr("transform", event.transform);
      });
    svg.call(zoom);

    /********************************************************
     * TOOLTIP
     ********************************************************/
    const tooltip = d3.select("#graph-tooltip");

    function handleMouseOver(event, d) {
      tooltip.style("display", "block")
        .html(`
          <strong>${d.title}</strong><br/>
          Authors: ${d.authors.join(", ")}<br/>
          Subject: ${d.category}
        `);
    }

    function handleMouseMove(event) {
      const tooltipRect = tooltip.node().getBoundingClientRect();
      const tooltipWidth = tooltipRect.width;
      const tooltipHeight = tooltipRect.height;
      const offset = 10;

      // Base position near cursor
      let xPos = event.pageX + offset;
      let yPos = event.pageY + offset;

      // Check right boundary
      if (xPos + tooltipWidth > window.innerWidth) {
        xPos = event.pageX - tooltipWidth - offset;
      }
      // Check bottom boundary
      if (yPos + tooltipHeight > window.innerHeight) {
        yPos = event.pageY - tooltipHeight - offset;
      }
      // Check left boundary
      if (xPos < 0) {
        xPos = offset;
      }
      // Check top boundary
      if (yPos < 0) {
        yPos = offset;
      }

      tooltip.style("left", xPos + "px").style("top", yPos + "px");
    }

    function handleMouseOut() {
      tooltip.style("display", "none");
    }

    function handleNodeClick(event, d) {
      document.getElementById("paper-title").textContent = d.title;
      document.getElementById("paper-authors").textContent = d.authors.join(", ");
      document.getElementById("paper-category").textContent = d.category;
      document.getElementById("paper-date").textContent = d.date;
      document.getElementById("paper-abstract").textContent = d.abstract;
      document.getElementById("paper-arxiv-link").href = `https://arxiv.org/abs/${d.arxivId}`;

      // Slide in details
      document.getElementById("details-panel").classList.remove("hidden");
    }

    /********************************************************
     * COLOR MODE SWITCH
     ********************************************************/
    const colorModePanel = document.getElementById("color-mode-panel");
    colorModePanel.addEventListener("change", (e) => {
      if (e.target.name === "colorMode") {
        currentColorMode = e.target.value;
        updateNodeColors();
      }
    });

    function updateNodeColors() {
      node.select("rect").attr("fill", d => getNodeColor(d));
    }

    /********************************************************
     * DETAILS PANEL
     ********************************************************/
    const closeBtn = document.getElementById("details-close-btn");
    closeBtn.addEventListener("click", () => {
      document.getElementById("details-panel").classList.add("hidden");
    });

    const summaryBtn = document.getElementById("paper-summary-btn");
    summaryBtn.addEventListener("click", () => {
      alert("Mock AI Summary:\nThis paper provides a thorough analysis and proposes an innovative approach. (Example)");
    });

    const newSearchBtn = document.getElementById("paper-new-search-btn");
    newSearchBtn.addEventListener("click", () => {
      document.getElementById("details-panel").classList.add("hidden");
      alert("Starting a new search... (mock)");
    });

    /********************************************************
     * CHAT INTERFACE
     ********************************************************/
    const chatMessagesEl = document.getElementById("chat-messages");
    const chatInputField = document.getElementById("chat-input-field");
    const chatSendBtn = document.getElementById("chat-send-btn");

    function addChatMessage(message, sender = "user") {
      const msgEl = document.createElement("div");
      msgEl.classList.add("chat-message", sender);
      msgEl.textContent = message;
      chatMessagesEl.appendChild(msgEl);
      chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
    }

    // Replace the mockBotResponse function with this:
    async function getBotResponse(userQuery) {
    try {
        const response = await fetch('http://melodic-jessa-naexya-deploy-3a0e4ff5.koyeb.app/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message: userQuery })
        });
        const data = await response.json();
        return data.response;
    } catch (error) {
        console.error('Chat error:', error);
        return "Sorry, I'm having trouble connecting to the server.";
    }
    }

    // Update the chatSendBtn event listener:
    chatSendBtn.addEventListener("click", async () => {
    const userQuery = chatInputField.value.trim();
    if (!userQuery) return;
    addChatMessage(userQuery, "user");
    chatInputField.value = "";

    const botMsg = await getBotResponse(userQuery);
    addChatMessage(botMsg, "bot");
    });

    chatSendBtn.addEventListener("click", () => {
      const userQuery = chatInputField.value.trim();
      if (!userQuery) return;
      addChatMessage(userQuery, "user");
      chatInputField.value = "";

      setTimeout(() => {
        const botMsg = mockBotResponse(userQuery);
        addChatMessage(botMsg, "bot");
      }, 600);
    });

    chatInputField.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        chatSendBtn.click();
      }
    });

    // Intro message
    addChatMessage(
      "Hello! I'm your Paper Assistant. Feel free to ask about any topic or paper!",
      "bot"
    );
  </script>
</body>
</html>
