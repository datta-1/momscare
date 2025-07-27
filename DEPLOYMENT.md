# MomCare AI Assistant - Vercel Deployment Guide

## Prerequisites

1. A Vercel account (sign up at [vercel.com](https://vercel.com))
2. Git repository (GitHub, GitLab, or Bitbucket)
3. Appwrite backend configured with the required databases and storage buckets

## Environment Variables

Before deploying, you need to set up the following environment variables in your Vercel project dashboard:

```
NEXT_PUBLIC_APPWRITE_ENDPOINT=your_appwrite_endpoint_here
NEXT_PUBLIC_APPWRITE_PROJECT_ID=your_appwrite_project_id_here
NEXT_PUBLIC_APPWRITE_BLOG_DATABASE_ID=your_blog_database_id_here
NEXT_PUBLIC_APPWRITE_BLOG_COLLECTION_ID=your_blog_collection_id_here
NEXT_PUBLIC_APPWRITE_PROFILE_BUCKET_ID=your_profile_bucket_id_here
NEXT_PUBLIC_APPWRITE_MEDICAL_BUCKET_ID=your_medical_bucket_id_here
```

## Deployment Steps

### Method 1: Deploy via Git (Recommended)

1. **Push your code to a Git repository** (GitHub, GitLab, or Bitbucket)

2. **Connect to Vercel:**
   - Go to [vercel.com](https://vercel.com) and sign in
   - Click "New Project"
   - Import your repository
   - Vercel will automatically detect it's a Next.js project

3. **Configure Environment Variables:**
   - In the deployment configuration, add all the environment variables listed above
   - You can also add them later in the project settings

4. **Deploy:**
   - Click "Deploy"
   - Vercel will build and deploy your application

### Method 2: Deploy via Vercel CLI

1. **Install Vercel CLI:**
   ```bash
   npm i -g vercel
   ```

2. **Login to Vercel:**
   ```bash
   vercel login
   ```

3. **Deploy from your project directory:**
   ```bash
   cd path/to/your/project
   vercel
   ```

4. **Set up environment variables:**
   ```bash
   vercel env add NEXT_PUBLIC_APPWRITE_ENDPOINT
   vercel env add NEXT_PUBLIC_APPWRITE_PROJECT_ID
   # ... add all other environment variables
   ```

5. **Redeploy to apply environment variables:**
   ```bash
   vercel --prod
   ```

## Important Notes

- The project has been configured to work optimally with Vercel's serverless functions
- Make sure your Appwrite backend is properly configured and accessible
- All environment variables starting with `NEXT_PUBLIC_` will be available in the browser
- The build process will fail if required environment variables are missing

## Troubleshooting

- **Build fails:** Check that all environment variables are set correctly
- **Appwrite connection issues:** Verify your Appwrite endpoint and project ID
- **File upload issues:** Ensure your Appwrite storage buckets are properly configured with correct permissions

## Post-Deployment

After successful deployment:
1. Test all features including file uploads, authentication, and database operations
2. Monitor the deployment logs in Vercel dashboard for any runtime errors
3. Set up domain (if needed) in Vercel project settings

Your MomCare AI Assistant will be available at the URL provided by Vercel (typically `https://your-project-name.vercel.app`).
