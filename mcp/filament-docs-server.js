import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import fetch from "node-fetch";
import cheerio from "cheerio";

const server = new Server(
    {
        name: "filament-docs",
        version: "0.1.0",
    },
    {
        capabilities: {
            tools: {
                "filament.search": {
                    description: "Search Filament docs pages",
                    inputSchema: {
                        type: "object",
                        properties: { q: { type: "string" } },
                        required: ["q"],
                    },
                },
                "filament.get": {
                    description: "Get a Filament docs page by path",
                    inputSchema: {
                        type: "object",
                        properties: { path: { type: "string" } },
                        required: ["path"],
                    },
                },
            },
        },
    }
);

const BASE = "https://filamentphp.com/docs";

server.setRequestHandler("tools/call", async (req) => {
    const { name, arguments: args } = req.params;

    if (name === "filament.search") {
        const q = encodeURIComponent(args.q ?? "");
        const url = `${BASE}/search?q=${q}`;
        const res = await fetch(url);
        const html = await res.text();
        const $ = cheerio.load(html);
        const results = [];
        $("a[href^='/docs/']").each((_, el) => {
            const title = $(el).text().trim();
            const path = $(el).attr("href");
            if (title && path) results.push({ title, path });
        });
        return {
            content: [{ type: "json", data: results.slice(0, 20) }],
        };
    }

    if (name === "filament.get") {
        const path = String(args.path || "").replace(/^\/+/, "");
        const url = `${BASE}/${path}`;
        const res = await fetch(url);
        if (!res.ok) {
            return { content: [{ type: "text", text: `HTTP ${res.status} for ${url}` }] };
        }
        const html = await res.text();
        const $ = cheerio.load(html);
        const pageTitle = $("h1").first().text().trim();
        const sections = [];
        $("h2, h3, h4").each((_, el) => {
            const heading = $(el).text().trim();
            const body = [];
            let sib = $(el).next();
            while (sib.length && !/^(H2|H3|H4)$/.test(sib.prop("tagName"))) {
                body.push(sib.text().trim());
                sib = sib.next();
            }
            sections.push({ heading, text: body.join("\n\n").trim() });
        });
        return {
            content: [{ type: "json", data: { title: pageTitle, sections } }],
        };
    }

    return {
        content: [{ type: "text", text: "Unknown tool" }],
    };
});

const transport = new StdioServerTransport();
server.connect(transport);


