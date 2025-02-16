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

        /* Style pour l'animation des points dans le chat */
    .loading-dots {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 10px;
        background-color: #e8e8e8;
        border-radius: 6px;
        width: fit-content;
        margin: 8px 0;
    }

    .dot {
        width: 8px;
        height: 8px;
        background-color: #666;
        border-radius: 50%;
        animation: bounce 0.8s infinite;
    }

    .dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    /* Animation de chargement circulaire */
    .circular-loader {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 40px;
        height: 40px;
        z-index: 1000;
    }

    .loader-dot {
        position: absolute;
        width: 8px;
        height: 8px;
        background-color: #007BFF;
        border-radius: 50%;
        animation: rotate 1.5s infinite;
    }

    .loader-dot:nth-child(1) { transform: rotate(0deg) translate(20px); }
    .loader-dot:nth-child(2) { transform: rotate(45deg) translate(20px); animation-delay: 0.125s; }
    .loader-dot:nth-child(3) { transform: rotate(90deg) translate(20px); animation-delay: 0.25s; }
    .loader-dot:nth-child(4) { transform: rotate(135deg) translate(20px); animation-delay: 0.375s; }
    .loader-dot:nth-child(5) { transform: rotate(180deg) translate(20px); animation-delay: 0.5s; }
    .loader-dot:nth-child(6) { transform: rotate(225deg) translate(20px); animation-delay: 0.625s; }
    .loader-dot:nth-child(7) { transform: rotate(270deg) translate(20px); animation-delay: 0.75s; }
    .loader-dot:nth-child(8) { transform: rotate(315deg) translate(20px); animation-delay: 0.875s; }

    @keyframes rotate {
        0% { opacity: 0.3; }
        50% { opacity: 1; }
        100% { opacity: 0.3; }
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal.show {
      display: flex;
      opacity: 1;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background-color: #fff;
      width: 95%;
      max-width: 1200px;
      height: 90vh;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      transform: translateY(-20px);
      transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
      transform: translateY(0);
    }

    .modal-header {
      padding: 1.5rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 1.5rem;
      color: #333;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 1.8rem;
      cursor: pointer;
      color: #666;
      padding: 0.5rem;
      line-height: 1;
    }

    .modal-close:hover {
      color: #333;
    }

    .modal-body {
      padding: 1.5rem;
      overflow-y: auto;
    }

    .paper-info {
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1.5rem;
    }

    .paper-info p {
      margin: 0.5rem 0;
    }

    .paper-content h3 {
      color: #333;
      margin: 1.5rem 0 1rem;
      font-size: 1.2rem;
    }

    .paper-content p {
      line-height: 1.6;
      color: #444;
    }

    .paper-actions {
      margin-top: 2rem;
      display: flex;
      gap: 1rem;
    }

    .modal-button {
      display: inline-block;
      padding: 0.6rem 1.2rem;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.2s;
    }

    .modal-button:hover {
      background-color: #0056b3;
    }

    /* Animation for loader */
    .modal-loader {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    .loader-spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #007BFF;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
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

  <div id="paper-modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modal-title"></h2>
        <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <div class="paper-info">
          <p><strong>Authors:</strong> <span id="modal-authors"></span></p>
          <p><strong>Subject:</strong> <span id="modal-category"></span></p>
          <p><strong>Publication Date:</strong> <span id="modal-date"></span></p>
          <p><strong>arXiv ID:</strong> <span id="modal-arxiv"></span></p>
        </div>
        <div class="paper-content">
          <h3>Abstract</h3>
          <p id="modal-abstract"></p>
          <h3>AI Summary</h3>
          <p id="modal-ai-summary"></p>
        </div>
        <div class="paper-actions">
          <a id="modal-arxiv-link" href="#" target="_blank" class="modal-button">View on arXiv</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    /********************************************************
     * MOCK DATA
     ********************************************************/
     const papersData = null;

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

    // Initialize groups at the global scope
    let linkGroup = svg.append("g").attr("class", "links");
    let nodeGroup = svg.append("g").attr("class", "nodes");

    // Add welcome message
    const welcomeGroup = svg.append("g")
      .attr("class", "welcome-message")
      .attr("transform", `translate(${width/2}, ${height/2})`);

    welcomeGroup.append("text")
      .attr("text-anchor", "middle")
      .attr("dominant-baseline", "middle")
      .attr("class", "welcome-title")
      .style("font-size", "24px")
      .style("fill", "#666")
      .text("Welcome to Paper Explorer!");

    welcomeGroup.append("text")
      .attr("text-anchor", "middle")
      .attr("dominant-baseline", "middle")
      .attr("y", 30)
      .style("font-size", "16px")
      .style("fill", "#888")
      .text("Start chatting with the AI to explore research papers");

    // Add zoom behavior
    const zoom = d3.zoom()
      .scaleExtent([0.5, 5])
      .on("zoom", event => {
        linkGroup.attr("transform", event.transform);
        nodeGroup.attr("transform", event.transform);
      });

    // Initially disable zoom
    svg.on(".zoom", null);

    function createGraph(papersData) {
      // Remove welcome message completely when graph is created
      if (papersData !== null) {
        svg.select(".welcome-message").remove();
        // Enable zoom only when we have data
        svg.call(zoom);
        // Center the view
        svg.call(zoom.transform, d3.zoomIdentity);
      } else {
        // Disable zoom when there's no data
        svg.on(".zoom", null);
      }
      
      // Clear existing graph
      linkGroup.selectAll("*").remove();
      nodeGroup.selectAll("*").remove();

      if (papersData !== null) {
        // Définition des liens originaux
        const linksData = [
          { source: "1", target: "2" },
          { source: "1", target: "3" },
          { source: "1", target: "4" },
          { source: "1", target: "5" },
          { source: "1", target: "6" },
          { source: "2", target: "3" },
          { source: "4", target: "6" },
          { source: "3", target: "6" }
        ];

        // Parse date for color scale
        const parseDate = d3.timeParse("%Y-%m-%d");
        papersData.forEach(d => {
          d.dateObj = parseDate(d.date);
          // Convert id to string to match linksData
          d.id = d.id.toString();
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
        const link = linkGroup
          .selectAll("line")
          .data(linksData)
          .enter()
          .append("line")
          .attr("stroke", "#999")
          .attr("stroke-opacity", 0.6)
          .attr("stroke-width", 2);

        // Nodes as (g) containing a rect + text
        const node = nodeGroup
          .selectAll("g")
          .data(papersData)
          .enter()
          .append("g")
          .on("mouseover", handleMouseOver)
          .on("mousemove", handleMouseMove)
          .on("mouseout", handleMouseOut)
          .on("click", handleNodeClick)
          .call(d3.drag()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

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

        // Drag functions
        function dragstarted(event) {
          if (!event.active) simulation.alphaTarget(0.3).restart();
          event.subject.fx = event.subject.x;
          event.subject.fy = event.subject.y;
        }

        function dragged(event) {
          event.subject.fx = event.x;
          event.subject.fy = event.y;
        }

        function dragended(event) {
          if (!event.active) simulation.alphaTarget(0);
          event.subject.fx = null;
          event.subject.fy = null;
        }

        // Pin node #1 to center
        simulation.on("tick", () => {
          link
            .attr("x1", d => d.source.x)
            .attr("y1", d => d.source.y)
            .attr("x2", d => d.target.x)
            .attr("y2", d => d.target.y);

          node.attr("transform", d => `translate(${d.x},${d.y})`);

          const centerNode = papersData.find(d => d.id === "1");
          if (centerNode) {
            centerNode.fx = width / 2;
            centerNode.fy = height / 2;
          }
        });

        // Update color mode handler
        function updateNodeColors() {
          nodeGroup.selectAll("rect").attr("fill", d => getNodeColor(d));
        }

        // Update color mode event listener
        const colorModePanel = document.getElementById("color-mode-panel");
        colorModePanel.addEventListener("change", (e) => {
          if (e.target.name === "colorMode") {
            currentColorMode = e.target.value;
            updateNodeColors();
          }
        });
      }
    }

    // Initial call to show welcome message
    createGraph(null);
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
        const response = await fetch('https://melodic-jessa-naexya-deploy-3a0e4ff5.koyeb.app/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message: userQuery })
        });
        const data = await response.json();
        
        createGraph([
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
    ]);
        return data.response;
    } catch (error) {
        console.error('Chat error:', error);
        return "Sorry, I'm having trouble connecting to the server.";
    }
    }


    // Fonction pour créer l'animation de points dans le chat
    function addLoadingMessage() {
        const loadingEl = document.createElement('div');
        loadingEl.classList.add('loading-dots', 'chat-message', 'bot');
        loadingEl.innerHTML = `
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        `;
        chatMessagesEl.appendChild(loadingEl);
        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
        return loadingEl;
    }

    // Fonction pour créer l'animation circulaire
    function showCircularLoader() {
        const loader = document.createElement('div');
        loader.classList.add('circular-loader');
        loader.innerHTML = `
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
            <div class="loader-dot"></div>
        `;
        document.body.appendChild(loader);
        return loader;
    }

    
    // Modification du gestionnaire d'événements du chat
    chatSendBtn.addEventListener("click", async () => {
        const userQuery = chatInputField.value.trim();
        if (!userQuery) return;
        
        addChatMessage(userQuery, "user");
        chatInputField.value = "";
        
        // Ajout des animations de chargement
        const loadingDots = addLoadingMessage();
        const circularLoader = showCircularLoader();
        
        try {
            const botMsg = await getBotResponse(userQuery);
            loadingDots.remove();
            circularLoader.remove();
            addChatMessage(botMsg, "bot");
        } catch (error) {
            loadingDots.remove();
            circularLoader.remove();
            addChatMessage("Sorry, something went wrong.", "bot");
        }
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

    // Modal management
    const modal = document.getElementById('paper-modal');
    const modalClose = modal.querySelector('.modal-close');

    // Close modal when clicking the close button
    modalClose.addEventListener('click', () => {
      modal.classList.remove('show');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('show');
      }
    });

    // Prevent modal close when clicking inside
    modal.querySelector('.modal-content').addEventListener('click', (e) => {
      e.stopPropagation();
    });

    // Mock AI summary function - replace with actual API call
    async function generateAISummary(paper) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve(`This paper by ${paper.authors.join(', ')} presents an innovative approach 
          to ${paper.category.toLowerCase()}. The research contributes significantly to the field 
          by addressing key challenges in ${paper.category.toLowerCase()} and proposing novel solutions. 
          The methodology is well-structured and the results show promising improvements over existing approaches.`);
        }, 1500);
      });
    }

    // Update the existing summaryBtn click handler
    const summary = document.getElementById("paper-summary-btn");
    summaryBtn.addEventListener("click", async () => {
      // Get current paper data from the details panel
      const currentPaper = {
        title: document.getElementById("paper-title").textContent,
        authors: document.getElementById("paper-authors").textContent.split(", "),
        category: document.getElementById("paper-category").textContent,
        date: document.getElementById("paper-date").textContent,
        abstract: document.getElementById("paper-abstract").textContent,
        arxivId: document.getElementById("paper-arxiv-link").href.split("/").pop()
      };

      // Update modal content
      document.getElementById("modal-title").textContent = currentPaper.title;
      document.getElementById("modal-authors").textContent = currentPaper.authors.join(", ");
      document.getElementById("modal-category").textContent = currentPaper.category;
      document.getElementById("modal-date").textContent = currentPaper.date;
      document.getElementById("modal-abstract").textContent = currentPaper.abstract;
      document.getElementById("modal-arxiv").textContent = currentPaper.arxivId;
      document.getElementById("modal-arxiv-link").href = `https://arxiv.org/abs/${currentPaper.arxivId}`;
      
      // Show modal
      modal.classList.add('show');
      
      // Add loading state for AI summary
      const aiSummaryEl = document.getElementById("modal-ai-summary");
      aiSummaryEl.innerHTML = '<div class="modal-loader"><div class="loader-spinner"></div></div>';
      
      // Generate and display AI summary
      try {
        const summary = await generateAISummary(currentPaper);
        aiSummaryEl.textContent = summary;
      } catch (error) {
        aiSummaryEl.textContent = "Sorry, failed to generate AI summary. Please try again later.";
      }
    });
  </script>
</body>
</html>
