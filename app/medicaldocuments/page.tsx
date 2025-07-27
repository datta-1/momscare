"use client";

import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";

export default function MedicalDocuments() {
  return (
    <motion.div 
      className="min-h-screen p-4 flex flex-col items-center justify-center"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
    >
      <Card className="max-w-xl w-full p-6 space-y-6">
        <h2 className="text-2xl font-bold text-center">Medical Documents</h2>
        <div className="flex flex-col items-center space-y-4">
          <p className="text-center text-gray-600">
            This is a demo page for medical document management. 
            In a full application, you would be able to upload and manage your medical documents here.
          </p>
          <Button variant="outline" disabled>
            Upload Document (Demo)
          </Button>
        </div>
        <p className="text-center mt-4 text-sm text-gray-500">
          No documents available in demo mode.
        </p>
      </Card>
    </motion.div>
  );
}