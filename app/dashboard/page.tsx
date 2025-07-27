"use client";

import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import Link from "next/link";

export default function Dashboard() {
  return (
    <div className="min-h-screen p-4">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-8">Dashboard</h1>
        
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">Profile</h3>
            <p className="text-gray-600 mb-4">
              This is a demo dashboard. In a full application, you would see your profile information here.
            </p>
            <Button variant="outline" disabled>
              Edit Profile (Demo)
            </Button>
          </Card>

          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">Medical Documents</h3>
            <p className="text-gray-600 mb-4">
              View and manage your medical documents.
            </p>
            <Button asChild>
              <Link href="/medicaldocuments">View Documents</Link>
            </Button>
          </Card>

          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">AI Chat</h3>
            <p className="text-gray-600 mb-4">
              Chat with your AI pregnancy care assistant.
            </p>
            <Button asChild>
              <Link href="/chat">Start Chat</Link>
            </Button>
          </Card>

          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">Appointments</h3>
            <p className="text-gray-600 mb-4">
              Schedule and manage your appointments.
            </p>
            <Button asChild>
              <Link href="/appointments">View Appointments</Link>
            </Button>
          </Card>

          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">Emergency</h3>
            <p className="text-gray-600 mb-4">
              Access emergency contacts and urgent care information.
            </p>
            <Button asChild>
              <Link href="/emergency">Emergency Info</Link>
            </Button>
          </Card>

          <Card className="p-6">
            <h3 className="text-xl font-semibold mb-4">Resources</h3>
            <p className="text-gray-600 mb-4">
              Browse educational resources and blog posts.
            </p>
            <Button asChild>
              <Link href="/resources">Browse Resources</Link>
            </Button>
          </Card>
        </div>
      </div>
    </div>
  );
}
