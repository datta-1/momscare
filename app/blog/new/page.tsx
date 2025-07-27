// Language: TSX
"use client";

import { useState } from "react";
import ReactMarkdown from "react-markdown";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default function NewBlogPost() {
  const [title, setTitle] = useState("");
  const [slug, setSlug] = useState("");
  const [content, setContent] = useState("");
  const [preview, setPreview] = useState(false);

  const generateSlug = (title: string) => {
    return title
      .toLowerCase()
      .replace(/[^a-z0-9 -]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  };

  const handleTitleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newTitle = e.target.value;
    setTitle(newTitle);
    setSlug(generateSlug(newTitle));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Demo mode - just show a message
    alert("This is demo mode. In a full application, your blog post would be saved here.");
  };

  return (
    <div className="min-h-screen p-4">
      <div className="max-w-4xl mx-auto">
        <Card className="p-6">
          <h1 className="text-2xl font-bold mb-6">Create New Blog Post (Demo)</h1>
          <p className="text-gray-600 mb-6">
            This is a demo page. In a full application, you would be able to create and publish blog posts here.
          </p>
          
          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <Label htmlFor="title">Title</Label>
              <Input
                id="title"
                type="text"
                value={title}
                onChange={handleTitleChange}
                placeholder="Enter blog post title"
              />
            </div>

            <div>
              <Label htmlFor="slug">Slug</Label>
              <Input
                id="slug"
                type="text"
                value={slug}
                onChange={(e) => setSlug(e.target.value)}
                placeholder="url-friendly-slug"
              />
            </div>

            <div>
              <Label htmlFor="content">Content (Markdown)</Label>
              <textarea
                id="content"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                placeholder="Write your blog post content in Markdown..."
                className="w-full h-64 p-3 border border-gray-300 rounded-md resize-y"
              />
            </div>

            <div className="flex gap-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => setPreview(!preview)}
              >
                {preview ? "Edit" : "Preview"}
              </Button>
              <Button type="submit" disabled>
                Publish (Demo)
              </Button>
            </div>
          </form>

          {preview && (
            <div className="mt-8 border-t pt-6">
              <h2 className="text-xl font-bold mb-4">Preview</h2>
              <div className="prose max-w-none">
                <h1>{title}</h1>
                <ReactMarkdown>{content}</ReactMarkdown>
              </div>
            </div>
          )}
        </Card>
      </div>
    </div>
  );
}