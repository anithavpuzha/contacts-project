<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::orderBy('id')->paginate(15);
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:contacts,phone',
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:contacts,phone,' . $contact->id,
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully!');
    }
    public function importXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file',
        ]);

        $mime = $request->file('xml_file')->getClientMimeType();
        $allowedMimes = ['text/xml', 'application/xml', 'application/octet-stream'];

        if (!in_array($mime, $allowedMimes)) {
            return back()->withErrors(['xml_file' => 'The uploaded file is not a valid XML file.']);
        }

        $xmlPath = $request->file('xml_file')->getRealPath();
        $xml = @simplexml_load_file($xmlPath);

        if (!$xml) {
            return back()->withErrors(['xml_file' => 'Invalid XML format.']);
        }

        $existingPhones = Contact::pluck('phone')->flip()->toArray();

        $imported = 0;
        $skipped = 0;
        $skippedRows = [];
        $rowNumber = 1;

        $newContacts = [];

        foreach ($xml->contact as $entry) {
            $name = trim((string) $entry->name);
            $phone = preg_replace('/\s+/', '', (string) $entry->phone);

            if (isset($existingPhones[$phone])) {
                $skipped++;
                $skippedRows[] = "Row {$rowNumber}";
            } else {
                $newContacts[] = [
                    'name' => $name,
                    'phone' => $phone,
                ];
                $existingPhones[$phone] = true;
            }

            $rowNumber++;
        }

        if (!empty($newContacts)) {
            Contact::insert($newContacts);
            $imported = count($newContacts);
        }

        $skippedMessage = $skipped > 0
            ? ' Skipped: ' . implode(', ', $skippedRows)
            : '';

        return redirect()
            ->route('contacts.index')
            ->with('success', "Imported: {$imported}, Skipped: {$skipped}.{$skippedMessage}");
    }

}
